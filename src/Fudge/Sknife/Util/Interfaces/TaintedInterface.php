<?php
namespace Fudge\Sknife\Util\Interfaces;

/**
 * Tainted functionnalities
 * Possiblity to flag a class' properties as modified
 * Should be used in conjunction of the Tainted trait
 * @author Yohann Marillet
 * @since 25/09/13
 */
interface TaintedInterface
{
    /**
     * Gets all tainted fields
     * @return array
     */
    public function getTainted();

    /**
     * Flag a field as tainted
     * @param  string    $field
     * @return null|self
     */
    public function setTainted($field);

    /**
     * Flag all fields as not tainted
     * @return null|self
     */
    public function resetTainted();

    /**
     * Check whether a field is tainted or not
     * @param  string $field
     * @return bool
     */
    public function isTainted($field);

    /**
     * Gets all taintable fields
     * @return array
     */
    public function getTaintable();

    /**
     * Check whether a field is taintable or not
     * @return bool
     */
    public function isTaintable($field);

    /**
     * Checks wheter one or more field has been modified
     * @return bool
     */
    public function hasTainted();

    /**
     * Untaint a field
     * @return void
     */
    public function untaint($name);
}
