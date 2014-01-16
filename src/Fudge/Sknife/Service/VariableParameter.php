<?php
namespace Fudge\Sknife\Service;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Translation\Translator;

/**
 * VariableParameter service
 * @author Yohann Marillet
 * @since 20/11/13
 */
class VariableParameter extends ContainerAware
{
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $token
     * @param $callable
     * @param array $options
     *
     * @return mixed
     * @author Yohann Marillet
     */
    public function get($token, $callable, $options=[])
    {
        if (!isset($options['translatable'])) {
            $options['translatable'] = true;
        }

        $content = $this->container->getParameter($token);
        if ($options['translatable']) {
            /** @var Translator $translatorService */
            $translatorService = $this->container->get('translator');
            $content = $translatorService->trans($content);
        }

        if (isset($options['callable_parameters'])) {
            $parameters = call_user_func_array($callable, $options['callable_parameters']);
        } else {
            $parameters = call_user_func($callable);
        }

        $return = str_replace(array_keys($parameters), array_values($parameters), $content);

        return $return;
    }
}
