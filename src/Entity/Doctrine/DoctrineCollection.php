<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Entity\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Novuso\Common\Domain\Model\Api\Collection;

/**
 * DoctrineCollection is a Doctrine collection adapter
 *
 * @copyright Copyright (c) 2016, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class DoctrineCollection extends ArrayCollection implements Collection
{
}
