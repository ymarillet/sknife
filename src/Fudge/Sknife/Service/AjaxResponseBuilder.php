<?php
namespace Fudge\Sknife\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * AjaxResponseBuilder
 * @author Yohann Marillet
 * @since 03/10/13
 */
class AjaxResponseBuilder
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
       $this->session = $session;
    }

    /**
     * @see JsonResponse::create
     *
     * @param null $data
     * @param int $status
     * @param array $headers
     *
     * @return JsonResponse
     * @author Yohann Marillet
     */
    public function get($data = null, $status = 200, $headers = array())
    {
        $responseData = array(
            'data' => $data,
            'messages' => $this->session->getFlashBag()->clear(),
            'type' => 'sknife.ajax',
        );

        $return = JsonResponse::create($responseData, $status, $headers);

        return $return;
    }
}
