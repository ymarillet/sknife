<?php
namespace Fudge\Sknife\EventListener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * SecurityListener
 * @author Yohann Marillet
 * @author Matt Drollette <matt@drollette.com>
 * @see https://matt.drollette.com/2012/07/user-specific-timezones-with-symfony2-and-twig-extensions/
 * @since 11/10/13
 */
class SecurityListener
{
    protected $security;
    protected $session;

    /**
     * Constructs a new instance of SecurityListener.
     *
     * @param SecurityContext $security The security context
     * @param Session         $session  The session
     */
    public function __construct(SecurityContext $security, Session $session)
    {
        $this->security = $security;
        $this->session = $session;
    }

    /**
     * Invoked after a successful login.
     *
     * @param InteractiveLoginEvent $event The event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        /*
        $timezone = $this->security->getToken()->getUser()->getTimezone();
        if (empty($timezone)) {
            $timezone = 'UTC';
        }
        */
        $timezone = date_default_timezone_get();
        $this->session->set('timezone', $timezone);
    }
}
