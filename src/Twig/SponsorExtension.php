<?php

namespace PHPSW\Twig;

class SponsorExtension extends \Twig_Extension
{
    public function __construct($sponsors)
    {
        $this->sponsors = $sponsors;
    }

    public function getFunctions()
    {
        return [
            'sponsors' => new \Twig_Function_Method($this, 'sponsors')
        ];
    }

    public function sponsors($event = null)
    {
        $date = $event ? $event->date : new \DateTime;

        $types = $this->sponsors;

        foreach ($types as $type => $sponsors) {
            $types[$type] = array_filter($sponsors, function ($sponsor) use ($date, $event, $type) {
                $sponsor = (object) $sponsor;

                $start = $sponsor->start ? \DateTime::createFromFormat('U', $sponsor->start) : null;
                $end   = $sponsor->end   ? \DateTime::createFromFormat('U', $sponsor->end)   : null;

                $active = ($date >= $start || !$start) && ($date <= $end || !$end);
                $host = $event && $event->venue && stripos($event->venue->name, $sponsor->name) !== false;

                return $host || $active && ($event && $type == 'meetup' || !$event);
            });
        }

        return $types;
    }

    public function getName()
    {
        return "sponsor";
    }
}
