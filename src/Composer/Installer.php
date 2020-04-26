<?php declare(strict_types=1);

namespace Mammatus\Http\Server\Composer;

use Chimera\Mapping\Routing;
use Chimera\Routing\Handler as RoutingHandler;
use Composer\Composer;
use Composer\Config;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Doctrine\Common\Annotations\AnnotationReader;
use Mammatus\Http\Server\Annotations\Bus as BusAnnotation;
use Mammatus\Http\Server\Annotations\Vhost as VhostAnnotation;
use Mammatus\Http\Server\Configuration\Bus;
use Mammatus\Http\Server\Configuration\Handler;
use Mammatus\Http\Server\Configuration\Server;
use Mammatus\Http\Server\Configuration\Vhost;
use Mammatus\Http\Server\HealthCheck\FetchHealtz;
use Mammatus\Http\Server\HealthCheck\HealthCheckVhost;
use React\EventLoop\StreamSelectLoop;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\Reflector\Exception\IdentifierNotFound;
use Roave\BetterReflection\SourceLocator\Type\Composer\Factory\MakeLocatorForComposerJsonAndInstalledJson;
use Roave\BetterReflection\SourceLocator\Type\Composer\Psr\Exception\InvalidPrefixMapping;
use Rx\Observable;
use Throwable;
use function ApiClients\Tools\Rx\observableFromArray;
use function array_key_exists;
use function Clue\React\Block\await;
use function count;
use function dirname;
use function explode;
use function file_exists;
use function is_array;
use function is_string;
use function microtime;
use function round;
use function rtrim;
use function Safe\chmod;
use function Safe\file_get_contents;
use function Safe\file_put_contents;
use function Safe\mkdir;
use function Safe\sprintf;
use function var_export;
use function WyriHaximus\getIn;
use function WyriHaximus\iteratorOrArrayToArray;
use function WyriHaximus\listClassesInDirectories;
use function WyriHaximus\Twig\render;
use const DIRECTORY_SEPARATOR;

final class Installer implements PluginInterface, EventSubscriberInterface
{
    private const ROUTE_BEHAVIOR = [
        Routing\CreateEndpoint::class          => RoutingHandler\CreateOnly::class,
        Routing\CreateAndFetchEndpoint::class  => RoutingHandler\CreateAndFetch::class,
        Routing\ExecuteEndpoint::class         => RoutingHandler\ExecuteOnly::class,
        Routing\ExecuteAndFetchEndpoint::class => RoutingHandler\ExecuteAndFetch::class,
        Routing\FetchEndpoint::class           => RoutingHandler\FetchOnly::class,
//        Routing\SimpleEndpoint::class          => 'none',
    ];

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [ScriptEvents::PRE_AUTOLOAD_DUMP => 'findVhosts'];
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
        // does nothing, see getSubscribedEvents() instead.
    }

    /**
     * Called before every dump autoload, generates a fresh PHP class.
     */
    public static function findVhosts(Event $event): void
    {
        $start    = microtime(true);
        $io       = $event->getIO();
        $composer = $event->getComposer();
        /** @psalm-suppress UnresolvableInclude */
        require_once $composer->getConfig()->get('vendor-dir') . '/react/promise/src/functions_include.php';
        /** @psalm-suppress UnresolvableInclude */
        require_once $composer->getConfig()->get('vendor-dir') . '/api-clients/rx/src/functions_include.php';
        /** @psalm-suppress UnresolvableInclude */
        require_once $composer->getConfig()->get('vendor-dir') . '/wyrihaximus/iterator-or-array-to-array/src/functions_include.php';
        /** @psalm-suppress UnresolvableInclude */
        require_once $composer->getConfig()->get('vendor-dir') . '/wyrihaximus/list-classes-in-directory/src/functions_include.php';
        /** @psalm-suppress UnresolvableInclude */
        require_once $composer->getConfig()->get('vendor-dir') . '/wyrihaximus/string-get-in/src/functions_include.php';
        /** @psalm-suppress UnresolvableInclude */
        require_once $composer->getConfig()->get('vendor-dir') . '/wyrihaximus/constants/src/Numeric/constants_include.php';
        /** @psalm-suppress UnresolvableInclude */
        require_once $composer->getConfig()->get('vendor-dir') . '/igorw/get-in/src/get_in.php';
        /** @psalm-suppress UnresolvableInclude */
        require_once $composer->getConfig()->get('vendor-dir') . '/jetbrains/phpstorm-stubs/PhpStormStubsMap.php';
        /** @psalm-suppress UnresolvableInclude */
        require_once $composer->getConfig()->get('vendor-dir') . '/thecodingmachine/safe/generated/filesystem.php';
        /** @psalm-suppress UnresolvableInclude */
        require_once $composer->getConfig()->get('vendor-dir') . '/thecodingmachine/safe/generated/strings.php';
        /** @psalm-suppress UnresolvableInclude */
        require_once $composer->getConfig()->get('vendor-dir') . '/wyrihaximus/simple-twig/src/functions_include.php';
        /** @psalm-suppress UnresolvableInclude */
        require_once $composer->getConfig()->get('vendor-dir') . '/react/promise-timer/src/functions_include.php';
        /** @psalm-suppress UnresolvableInclude */
        require_once $composer->getConfig()->get('vendor-dir') . '/clue/block-react/src/functions_include.php';

        $io->write('<info>mammatus/http-server:</info> Locating vhosts');

        $vhosts = self::findAllVhosts($composer, $io);

        $classContents = render(
            file_get_contents(
                self::locateRootPackageInstallPath($composer->getConfig(), $composer->getPackage()) . '/etc/AbstractConfiguration.php.twig'
            ),
            [
                'servers' => $vhosts,
            ]
        );

        $installPath   = self::locateRootPackageInstallPath($composer->getConfig(), $composer->getPackage())
            . '/src/Generated/AbstractConfiguration.php';

        file_put_contents($installPath, $classContents);
        chmod($installPath, 0664);

        $io->write(sprintf(
            '<info>mammatus/http-server:</info> Generated static abstract vhost(s) configuration in %s second(s)',
            round(microtime(true) - $start, 2)
        ));
    }

    /**
     * Find the location where to put the generate PHP class in.
     */
    private static function locateRootPackageInstallPath(
        Config $composerConfig,
        RootPackageInterface $rootPackage
    ): string {
        // You're on your own
        if ($rootPackage->getName() === 'mammatus/http-server') {
            return dirname($composerConfig->get('vendor-dir'));
        }

        return $composerConfig->get('vendor-dir') . '/mammatus/http-server';
    }

    /**
     * @return array<string, array<array{class: string, method: string, static: bool}>>
     */
    private static function findAllVhosts(Composer $composer, IOInterface $io): array
    {
        $annotationReader = new AnnotationReader();
        $vendorDir = $composer->getConfig()->get('vendor-dir');
        retry:
        try {
            $classReflector = new ClassReflector(
                (new MakeLocatorForComposerJsonAndInstalledJson())(dirname($vendorDir), (new BetterReflection())->astLocator()),
            );
        } catch (InvalidPrefixMapping $invalidPrefixMapping) {
            mkdir(explode('" is not a', explode('" for prefix "', $invalidPrefixMapping->getMessage())[1])[0]);
            goto retry;
        }

        $result     = [];
        $packages   = $composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
        $packages[] = $composer->getPackage();
        $classes = fn () => self::classes($packages, $vendorDir, $classReflector, $io);
        ($classes())->filter(static function (ReflectionClass $class): bool {
            return $class->implementsInterface(Vhost::class);
        })->
            map(fn (ReflectionClass $class): string => $class->getName())->
            map(fn (string $vhost): Vhost => new $vhost())->
            toArray()->
            toPromise()->
        then(static function (array $flatVhosts) use (&$result, $io, $classes, $annotationReader): void {
            $io->write(sprintf('<info>mammatus/http-server:</info> Found %s vhost(s)', count($flatVhosts)));
            $vhosts = [];

            foreach ($flatVhosts as $vhost) {
                assert($vhost instanceof Vhost);
                $vhosts[] = new Server(
                    $vhost,
                    ...await(
                        ($classes())->flatMap(static function (ReflectionClass $class) use ($annotationReader): Observable {
                            $annotations = [];
                            foreach ($annotationReader->getClassAnnotations(new \ReflectionClass($class->getName())) as $annotation) {
                                $annotations[get_class($annotation)] = $annotation;
                            }
                            return observableFromArray([[
                                'class' => $class->getName(),
                                'annotations' => $annotations,
                            ]]);
                        })->filter(static function (array $classNAnnotations): bool {
                            if (!array_key_exists(VhostAnnotation::class, $classNAnnotations['annotations'])) {
                                return false;
                            }

                            if (!array_key_exists(BusAnnotation::class, $classNAnnotations['annotations'])) {
                                return false;
                            }

                            foreach ($classNAnnotations['annotations'] as $annotation) {
                                if (is_subclass_of($annotation, Routing\Endpoint::class)) {
                                    return true;
                                }
                            }

                            return false;
                        })->filter(function (array $classNAnnotations) use ($vhost): bool {
                            return $classNAnnotations['annotations'][VhostAnnotation::class]->vhost() === $vhost->name();
                        })->toArray()->toPromise()->then(function (array $handlers) {
                            $busses = [];
                            foreach ($handlers as $handler) {
                                $busses[$handler['annotations'][BusAnnotation::class]->bus()][] = $handler;
                            }
                            $busInstances = [];
                            foreach ($busses as $name => $handlers) {
                                $busInstances[] = new Bus(
                                    $name,
                                    ...array_map(
                                        function (array $handler) {
                                            foreach ($handler['annotations'] as $annotation) {
                                                if (is_subclass_of($annotation, Routing\Endpoint::class)) {
                                                    $endpoint = $annotation;
                                                    break;
                                                }
                                            }

                                            return new Handler(
                                                $endpoint->methods,
                                                $endpoint->query,
                                                $handler['class'],
                                                self::ROUTE_BEHAVIOR[get_class($endpoint)],
                                                $endpoint->path,
                                            );
                                        },
                                        array_values($handlers),
                                    ),
                                );
                            }

                            return $busInstances;
                        }),
                        new StreamSelectLoop(),
                        1
                    )
                );
            }

            $result = $vhosts;
        })->then(null, static function (Throwable $throwable) use ($io): void {
            $io->write(sprintf('<info>mammatus/http-server:</info> Unexpected error: <fg=red>%s</>', (string)$throwable));
        });

        return $result;
    }

    private static function classes(array $packages, string $vendorDir, ClassReflector $classReflector, IOInterface $io): Observable
    {
        return observableFromArray($packages)->filter(static function (PackageInterface $package): bool {
            return count($package->getAutoload()) > 0;
        })->filter(static function (PackageInterface $package): bool {
            return getIn($package->getExtra(), 'mammatus.http.server.has-vhosts', false);
        })->filter(static function (PackageInterface $package): bool {
            return array_key_exists('classmap', $package->getAutoload()) || array_key_exists('psr-4', $package->getAutoload());
        })->flatMap(static function (PackageInterface $package) use ($vendorDir): Observable {
            $packageName = $package->getName();
            $autoload    = $package->getAutoload();
            $paths       = [];
            foreach (['classmap', 'psr-4'] as $item) {
                if (! array_key_exists($item, $autoload)) {
                    continue;
                }

                foreach ($autoload[$item] as $path) {
                    if (is_string($path)) {
                        if ($package instanceof RootPackageInterface) {
                            $paths[] = dirname($vendorDir) . DIRECTORY_SEPARATOR . $path;
                        } else {
                            $paths[] = $vendorDir . DIRECTORY_SEPARATOR . $packageName . DIRECTORY_SEPARATOR . $path;
                        }
                    }

                    if (! is_array($path)) {
                        continue;
                    }

                    foreach ($path as $p) {
                        if ($package instanceof RootPackageInterface) {
                            $paths[] = dirname($vendorDir) . DIRECTORY_SEPARATOR . $p;
                        } else {
                            $paths[] = $vendorDir . DIRECTORY_SEPARATOR . $packageName . DIRECTORY_SEPARATOR . $p;
                        }
                    }
                }
            }

            return observableFromArray($paths);
        })->map(static function (string $path): string {
            return rtrim($path, '/');
        })->filter(static function (string $path): bool {
            return file_exists($path);
        })->toArray()->flatMap(static function (array $paths): Observable {
            return observableFromArray(
                iteratorOrArrayToArray(
                    listClassesInDirectories(...$paths)
                )
            );
        })->flatMap(static function (string $class) use ($classReflector, $io): Observable {
            try {
                /** @psalm-suppress PossiblyUndefinedVariable */
                return observableFromArray([
                    (static function (ReflectionClass $reflectionClass): ReflectionClass {
                        $reflectionClass->getInterfaces();
                        $reflectionClass->getMethods();

                        return $reflectionClass;
                    })($classReflector->reflect($class)),
                ]);
            } catch (IdentifierNotFound $identifierNotFound) {
                $io->write(sprintf(
                    '<info>mammatus/http-server:</info> Error while reflecting "<fg=cyan>%s</>": <fg=yellow>%s</>',
                    $class,
                    $identifierNotFound->getMessage()
                ));
            }

            return observableFromArray([]);
        })->filter(static function (ReflectionClass $class): bool {
            return $class->isInstantiable();
        });
    }
}
