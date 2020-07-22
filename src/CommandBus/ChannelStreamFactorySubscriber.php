<?php declare(strict_types=1);

namespace Mammatus\Http\Server\CommandBus;

use Ancarda\Psr7\StringStream\ReadOnlyStringStream;
use League\Tactician\Handler\CommandHandlerMiddleware;
use parallel\Channel;
use Psr\Http\Message\ResponseInterface;
use React\Promise\PromiseInterface;

final class ChannelStreamFactorySubscriber
{
    private Channel $input;
    private CommandHandlerMiddleware $commandHandlerMiddleware;

    public function __construct(Channel $input, CommandHandlerMiddleware $commandHandlerMiddleware)
    {
        $this->input = $input;
        $this->commandHandlerMiddleware = $commandHandlerMiddleware;
    }

    public function __invoke($command): void
    {
        $result = $this->commandHandlerMiddleware->execute(unserialize($command), function () {});
        if ($result instanceof PromiseInterface) {
            $result->then(fn (ResponseInterface $response) => $this->input->send($this->swapOutResponseBody($response)));
            return;
        }

        $this->input->send($this->swapOutResponseBody($result));
    }

    private function swapOutResponseBody(ResponseInterface $response): ResponseInterface
    {
        return $response->withBody(
            new ReadOnlyStringStream(
                (string)$response->getBody()
            )
        );
    }
}
