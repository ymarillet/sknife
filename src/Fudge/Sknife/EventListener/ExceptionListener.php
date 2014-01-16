<?php
namespace Fudge\Sknife\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Fudge\Sknife\Exception\PermissionRequiredException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * ExceptionListener
 * @author Yohann Marillet
 * @since 13/12/13
 */
class ExceptionListener
{
    /** @var array */
    protected $redirections = [];

    public function __construct($redirections=[]) {
        $this->setRedirections($redirections);
    }

    protected function setRedirections(array $redirections) {
        foreach($redirections as $v) {
            $this->addRedirection($v['class'],$v['action']);
        }
    }

    protected function addRedirection($exceptionClass, $action) {
        $this->redirections[$exceptionClass] = $action;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $exceptionClass = get_class($exception);

        if(isset($this->redirections[$exceptionClass]) && !empty($this->redirections[$exceptionClass])) {
            switch(true) {
                case ($exception instanceof PermissionRequiredException):
                    break;
                default:
                    return;
            }

            $kernel = $event->getKernel();
            $request = $event->getRequest()->duplicate(null, null, [
                                                                    '_controller' => $this->redirections[$exceptionClass],
                                                                    'exception' => $exception,
                                                                   ]);
            $response = $kernel->handle($request, HttpKernelInterface::SUB_REQUEST, false);

            $event->setResponse($response);
        }
    }
}