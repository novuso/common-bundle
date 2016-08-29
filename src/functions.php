<?php
/**
 * This file is part of the Novuso Framework
 *
 * @copyright Copyright (c) 2016, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */

use Novuso\Common\Domain\Model\Basic\MbString;
use Novuso\Common\Domain\Model\Basic\StdString;
use Novuso\System\Collection\ArrayCollection;
use Novuso\System\Exception\DomainException;
use Novuso\System\Type\Arrayable;
use Novuso\System\Utility\Test;
use Novuso\System\Utility\VarPrinter;

if (!function_exists('std_string')) {
    /**
     * Created StdString instance
     *
     * @param mixed $value The string value
     *
     * @return StdString
     *
     * @throws DomainException When the value is not valid
     */
    function std_string($value): StdString
    {
        if (!Test::isStringCastable($value)) {
            $message = sprintf('Invalid string value: %s', VarPrinter::toString($value));
            throw new DomainException($message);
        }

        if ($value instanceof StdString) {
            return $value;
        }

        /** @var StdString $string */
        $string = StdString::create((string) $value);

        return $string;
    }
}

if (!function_exists('mb_string')) {
    /**
     * Creates MbString instance
     *
     * @param mixed $value The string value
     *
     * @return MbString
     *
     * @throws DomainException When the value is not valid
     */
    function mb_string($value): MbString
    {
        if (!Test::isStringCastable($value)) {
            $message = sprintf('Invalid string value: %s', VarPrinter::toString($value));
            throw new DomainException($message);
        }

        if ($value instanceof MbString) {
            return $value;
        }

        /** @var MbString $string */
        $string = MbString::create((string) $value);

        return $string;
    }
}

if (!function_exists('collect')) {
    /**
     * Creates an ArrayCollection instance
     *
     * @param Arrayable|array $items The items to collect
     *
     * @return ArrayCollection
     *
     * @throws DomainException When the items are not valid
     */
    function collect($items = []): ArrayCollection
    {
        if (!is_array($items)) {
            if (!($items instanceof Arrayable)) {
                $message = sprintf('Invalid items: %s', VarPrinter::toString($items));
                throw new DomainException($message);
            }
            $items = $items->toArray();
        }

        return ArrayCollection::create($items);
    }
}
