<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Response;

class MemberController extends AbstractController
{
    public function photoAction(Application $app, $id, $size)
    {
        $member = $app['meetup.client']->getMember($id);

        if (!$member) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
        }

        $photo = $member->photo;

        switch ($size) {
            case 'highres':
                if (isset($photo->highres_link)) {
                    $size = 'highres';
                    break;
                }
            case 'photo':
                if (isset($photo->photo_link)) {
                    $size = 'photo';
                    break;
                }
            default:
                $size = 'thumb';
        }

        try {
            $response = $app['guzzle']->get($member->photo->{"${size}_link"});
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $app->abort($e->getResponse()->getStatusCode());
        }

        return new Response($response->getBody(), $response->getStatusCode(), [
            'Cache-Control' => 'public',
            'Content-Type' => (string) $response->getHeader('Content-Type'),
            'Expires' => (new \DateTime('+2 weeks'))->format('D, d M Y H:i:s T')
        ]);
    }
}
