<?php
namespace Fudge\Sknife\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * DateRange
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 21/11/13
 * @Assert\Callback(methods={"checkDates"}, groups={"checkDates"})
 * @Assert\Callback(methods={"endDateMustBeTodayOrBefore"}, groups={"endDateMustBeTodayOrBefore"})
 * @Assert\Callback(methods={"startDateMustBeTodayOrAfter"}, groups={"startDateMustBeTodayOrAfter"})
 * @Assert\Callback(methods={"emptyDates"}, groups={"emptyDates"})
 */
class DateRange
{
    /**
     * @var \DateTime
     *
     */
    protected $dateStart;

    /**
     * @var \DateTime
     *
     */
    protected $dateEnd;

    /**
     * @var \DateInterval
     */
    protected $dateInterval;

    /**
     * @var \DatePeriod
     */
    protected $datePeriod;

    /**
     * @var \DateTimeZone
     */
    protected $timezone;

    /**
     * @var bool
     */
    protected $includeStartDate = true;

    /**
     * @var bool
     */
    protected $includeEndDate = true;

    /**
     * @var bool
     */
    protected $usePeriod = true;

    /**
     * @var string
     */
    private $datesViolationsAt = null;

    /**
     * @param \DateTime|null     $dateStart
     * @param \DateTime|null     $dateEnd
     * @param \DateInterval|null $dateInterval
     * @param string|null        $timezone
     * @param array              $options
     */
    public function __construct($dateStart = null, $dateEnd = null, $dateInterval = null, $timezone = null, $options=[])
    {
        if (!isset($options['defaults'])) {
            $options['defaults'] = [];
        }

        if (is_null($timezone)) {
            $timezone = date_default_timezone_get();
        }
        $this->timezone = new \DateTimeZone($timezone);

        if (is_null($dateStart)) {
            $this->dateStart = new \DateTime('now', $this->timezone);
            $this->dateStart->setTime(0,0,0);
        } else {
            $this->dateStart = $dateStart;
            $this->dateStart->setTimezone($this->timezone);
        }

        if (is_null($dateEnd)) {
            if (!isset($options['defaults']['dateEnd'])) {
                $options['defaults']['dateEnd'] = '+1 week';
            }
            $this->dateEnd = new \DateTime($options['defaults']['dateEnd'], $this->timezone);
            $this->dateEnd->setTime(0,0,0);
        } else {
            $this->dateEnd = $dateEnd;
            $this->dateEnd->setTimezone($this->timezone);
        }

        if (is_null($dateInterval)) {
            if (!isset($options['defaults']['dateInterval'])) {
                $options['defaults']['dateInterval'] = 'P1D';
            }
            $this->dateInterval = new \DateInterval($options['defaults']['dateInterval']);
        } else {
            $this->dateInterval = $dateInterval;
        }

        if (isset($options['usePeriod'])) {
            $this->usePeriod = (true==$options['usePeriod']);
        }

        if (isset($options['defaults']['includeStartDate'])) {
            $this->includeStartDate = (true==$options['defaults']['includeStartDate']);
        }

        if (isset($options['defaults']['includeEndDate'])) {
            $this->includeEndDate = (true==$options['defaults']['includeEndDate']);
        }

        $this->rebuildPeriod();
    }

    protected function rebuildPeriod()
    {
        if ($this->usePeriod && $this->dateStart instanceof \DateTime && $this->dateEnd instanceof \DateTime) {
            $oneDayInterval = new \DateInterval('P1D');
            $dateStart = $this->dateStart;
            if (!$this->includeStartDate) {
                $dateStart = clone $dateStart;
                $dateStart->add($oneDayInterval);
            }
            $dateEnd = $this->dateEnd;
            if ($this->includeEndDate) {
                $dateEnd = clone $dateEnd;
                $dateEnd->add($oneDayInterval);
            }
            $this->datePeriod = new \DatePeriod($dateStart, $this->dateInterval, $dateEnd);
        }
    }

    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;
        $this->rebuildPeriod();

        return $this; //fluent interface
    }

    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    public function setDateInterval($dateInterval)
    {
        $this->dateInterval = $dateInterval;
        $this->rebuildPeriod();

        return $this; //fluent interface
    }

    public function getDateInterval()
    {
        return $this->dateInterval;
    }

    public function getDatePeriod()
    {
        return $this->datePeriod;
    }

    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;
        $this->rebuildPeriod();

        return $this; //fluent interface
    }

    public function getDateStart()
    {
        return $this->dateStart;
    }

    public function setIncludeEndDate($includeEndDate)
    {
        $this->includeEndDate = $includeEndDate;
        $this->rebuildPeriod();

        return $this; //fluent interface
    }

    public function getIncludeEndDate()
    {
        return $this->includeEndDate;
    }

    public function setIncludeStartDate($includeStartDate)
    {
        $this->includeStartDate = $includeStartDate;
        $this->rebuildPeriod();

        return $this; //fluent interface
    }

    public function getIncludeStartDate()
    {
        return $this->includeStartDate;
    }

    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
        $this->rebuildPeriod();

        return $this; //fluent interface
    }

    public function getTimezone()
    {
        return $this->timezone;
    }

    public function setDatesViolationsAt($datesViolationsAt)
    {
        $this->datesViolationsAt = $datesViolationsAt;

        return $this; //fluent interface
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function checkDates(ExecutionContextInterface $context)
    {
        if ($this->dateStart instanceof \DateTime && $this->dateEnd instanceof \DateTime) {
            if ($this->dateStart >= $this->dateEnd) {
                $context->addViolationAt($this->datesViolationsAt, 'La date de début doit être strictement inférieure à la date de fin');
            }
        }
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function endDateMustBeTodayOrBefore(ExecutionContextInterface $context)
    {
        if ($this->dateEnd instanceof \DateTime) {
            $tomorrow = new \DateTime();
            $tomorrow->setTime(0,0,0);
            $tomorrow->add(new \DateInterval('P1D'));
            if ($this->dateEnd >= $tomorrow) {
                $context->addViolationAt($this->datesViolationsAt, 'La date de fin doit être inférieure à la date d\'aujourd\'hui');
            }
        }
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function startDateMustBeTodayOrAfter(ExecutionContextInterface $context)
    {
        if ($this->dateStart instanceof \DateTime) {
            $today = new \DateTime();
            $today->setTime(0,0,0);
            if ($this->dateStart < $today) {
                $context->addViolationAt($this->datesViolationsAt, 'La date de début doit être supérieure ou égale à la date d\'aujourd\'hui');
            }
        }
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function emptyDates(ExecutionContextInterface $context)
    {
        if (empty($this->dateStart)) {
            $context->addViolationAt($this->datesViolationsAt, 'La date de début ne peut être vide');
        }

        if (empty($this->dateEnd)) {
            $context->addViolationAt($this->datesViolationsAt, 'La date de fin ne peut être vide');
        }
    }
}
