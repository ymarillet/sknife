<?php
namespace Fudge\Sknife\Exception;

/**
 * PermissionRequiredException
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 12/12/13
 */
class PermissionRequiredException extends BusinessException
{
    /** @var array */
    protected $requiredPermissions=[];
    /** @var array */
    protected $currentPermissions=[];

    public function setRequiredPermissions(Array $requiredPermissions) {
        $this->requiredPermissions = $requiredPermissions;
        $this->buildMessage();
        return $this;
    }

    public function setCurrentPermissions(Array $currentPermissions) {
        $this->currentPermissions = $currentPermissions;
        $this->buildMessage();
        return $this;
    }

    public function buildMessage() {
        if(!empty($this->requiredPermissions)) {
            $this->message = 'The following permissions are required to access this resource: ['.implode(', ',$this->requiredPermissions).'].';

            if(!empty($this->currentPermissions)) {
                $this->message .= ' You currently have: ['.implode(', ',$this->currentPermissions).']';
            }
        } else {
            $this->message = 'You are missing some permission to access this resource';
        }
    }
} 