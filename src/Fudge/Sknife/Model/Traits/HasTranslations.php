<?php
namespace Fudge\Sknife\Model\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use O2K\Minisites\ApplicationBundle\Model\Language;

/**
 * HasTranslations
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 20/10/13
 */
trait HasTranslations
{
//    /**
//     * @var \Doctrine\Common\Collections\Collection
//     * @ORM\OneToMany(
//     *   targetEntity="Path\To\Your\Translation\Entity",
//     *   mappedBy="object",
//     *   cascade={"persist", "remove"}
//     * )
//     */
//    protected $translations;

    /**
     * Get the translations
     *
     * @since 16/10/2013
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}
