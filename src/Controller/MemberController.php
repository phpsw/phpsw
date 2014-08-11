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
}
