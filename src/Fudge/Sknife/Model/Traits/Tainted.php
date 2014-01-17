<?php
namespace Fudge\Sknife\Model\Traits;

use Fudge\Sknife\Exception\BusinessException;

/**
 * Tainted functionnalities
 * Possibility to flag a class' properties as modified
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @see Fudge\Sknife\Util\TaintedInterface
 */
trait Tainted
{
    protected $tainted;

    /**
     * @inheritdoc
     */
    public function getTainted()
    {
        $return = array();
        foreach ($this->tainted as $tainted) {
            $return[$tainted] = $this->$tainted;
        }

        return $return;
    }

    /**
     * @inheritdoc
     */
    public function resetTainted()
    {
        $this->tainted = array();
    }

    /**
     * @inheritdoc
     */
    public function isTainted($field)
    {
        return isset($this->tainted[$field]);
    }

    /**
     * @inheritdoc
     */
    public function setTainted($field)
    {
        if (!$this->isTaintable($field)) {
            throw new BusinessException('The field "' . $field . '" is not in the taintable field list (' . get_class($this) . ')');
        }
        $this->tainted[$field] = $field;
    }

    /**
     * @inheritdoc
     */
    public function getTaintable()
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function isTaintable($field)
    {
        $taintable = $this->getTaintable();

        return isset($taintable[$field]);
    }

    /**
     * @inheritdoc
     */
    public function hasTainted()
    {
        return !empty($this->tainted);
    }

    /**
     * @inheritdoc
     */
    public function untaint($name)
    {
        if (isset($this->tainted[$name])) {
            unset($this->tainted[$name]);
        }
    }
}
