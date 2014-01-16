<?php

namespace Fudge\Sknife\Authorization\Voter;

use Fudge\Sknife\Model\Interfaces\HasPermissionsInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * PermissionVoter votes if any attribute starts with a given prefix.
 *
 * @author Yohann Marillet
 * @see RoleVoter
 */
class PermissionVoter implements VoterInterface
{
    protected $prefix;

    /**
     * Constructor.
     *
     * @param string $prefix The role prefix
     */
    public function __construct($prefix = 'PERMISSION_')
    {
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return 0 === strpos($attribute, $this->prefix);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return ($class instanceof HasPermissionsInterface);
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;
        if($this->supportsClass($token->getUser())) {
            $permissions = $this->extractPermissions($token);

            foreach ($attributes as $attribute) {
                if (!$this->supportsAttribute($attribute)) {
                    continue;
                }

                $result = VoterInterface::ACCESS_GRANTED;
                if(!in_array($attribute, $permissions)) {
                    $result = VoterInterface::ACCESS_DENIED;
                    break;
                }
            }
        }

        return $result;
    }

    protected function extractPermissions(TokenInterface $token)
    {
        /** @var HasPermissionsInterface $user */
        $user = $token->getUser();

        $return = $user->getPermissions();

        return $return;
    }
}
