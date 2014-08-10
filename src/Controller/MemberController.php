<?php

namespace PHPSW\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class MemberController extends AbstractController
{
    public function indexAction(Application $app, Request $request)
    {
        $members = $app['meetup.client']->getMembers();

        $sort_by = $request->get('sort_by', 'date_joined_desc');

        foreach (current($members)->answers as $i => $question) {
            if ($request->get("question_{$question->question_id}_positive")) {
                $members = array_filter($members, function ($member) use ($i) {
                    $question = $member->answers[$i];
                    $answer = isset($question->answer) ? $question->answer : null;

                    return strlen($answer) > 2 && !(preg_match('#^no#i', $answer) || preg_match('#empty|inexperienced#i', $answer));
                });
            }
        }

        switch ($sort_by) {
            case 'name_asc':
                uasort($members, function ($a, $b) {
                    return strcasecmp($a->name, $b->name);
                });

                break;

            case 'name_desc':
                uasort($members, function ($a, $b) {
                    return strcasecmp($b->name, $a->name);
                });

                break;

            case 'date_joined_asc':
                uasort($members, function ($a, $b) {
                    return $a->joined > $b->joined;
                });

                break;

            case 'date_joined_desc':
                uasort($members, function ($a, $b) {
                    return $a->joined < $b->joined;
                });

                break;
        }

        return $this->render($app, 'members.html.twig', [
            'members'    => $members,
            'sort_by'    => $sort_by
        ]);
    }

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
