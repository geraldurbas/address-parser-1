<?php

namespace TreeHouse\AddressParser;

use TreeHouse\AddressParser\Exception\InvalidAddressException;

class AddressParser
{
    /**
     * Start with potential numbers, followed by (at least one) alphabetic character(s),
     * and excluding any unexpected characters (#, @, %, etc.)
     *
     * @var string
     */
    protected $regexStreetName         = '\d*[a-zA-Z][^\d\$\%\@\#\*]';

    /**
     * The first following number(s) preceded by a letter, dash or space
     *
     * @var string
     */
    protected $regexHouseNumber        = '(?<=[a-z\- ])[0-9]';

    /**
     * Followed by pretty much everything else:
     * the special characters should've been stripped already at this point
     *
     * @var string
     */
    protected $regexHouseNumberSuffix = '[a-z0-9\-\/\s\+,\.\(\)#&]';

    /**
     * Composed regex
     *
     * @var string
     */
    protected $addressRegex;

    /**
     * Keysword that will be stripped during parsing
     *
     * @var array
     */
    protected $strippableKeywords = array(
        'recreatie',
        'verlaagd',
    );

    /**
     * @var array
     */
    protected $specialTokenMap = array(
        '\'t' => 'HET_TOKEN',
        '\'s' => 'SCH_TOKEN',
        '\'s-' => 'SCH_DASH_TOKEN',
    );

    /**
     * Holds processed keywords as a regular expression
     *
     * @see self::getKeywords()
     *
     * @var string
     */
    protected $keywords;

    /**
     * Constructor.
     */
    public function __construct()
    {
        // compose full address regex
        $this->addressRegex = sprintf(
            '/^(%s+)((%s+)(%s*))?$/i',
            $this->regexStreetName,
            $this->regexHouseNumber,
            $this->regexHouseNumberSuffix
        );
    }

    /**
     * Parses an address into parts
     *
     * @param string $address
     *
     * @throws \RuntimeException when it is unable to parse the address
     *
     * @return array|null array with the following keys: street, number, address or null when an
     *                    empty address is given
     */
    public function parse($address)
    {
        if (!$address) {
            return null;
        }

        $address = $this->normalizeAddress($address);

        if (!preg_match($this->addressRegex, $address, $matches)) {
            throw new InvalidAddressException(sprintf('Unabled to parse address "%s"', $address));
        }

        // trim
        $matches = array_map(function ($match) {
            return trim($match, ' -./');
        }, $matches);

        // ignore warnings about unset arguments (this can happen because
        // the pattern has optional fields)
        @list($fullmatch, $street, /* number group */, $number, $numberSuffix) = $matches;

        // validate numberSuffix
        if (preg_match('/[a-z]{5,}/i', $numberSuffix)) {
            throw new InvalidAddressException(sprintf('Unabled to parse number suffix "%s"', $numberSuffix));
        }

        $streetNumber = $this->normalizeNumber($number, $numberSuffix);

        $street = strtr($street, array_flip($this->specialTokenMap));

        return array(
            'street'  => trim($street),
            'number'  => $streetNumber,
            'address' => trim(sprintf('%s %s', $street, $streetNumber)),
        );
    }

    /**
     * Parses an address
     *
     * @param  string $address
     *
     * @return string
     */
    protected function normalizeAddress($address)
    {
        // decode address to convert html entities ("&whatever;", "&#x1234;", etc)
        // to their unicode equivalents.
        $address = trim(html_entity_decode($address, ENT_NOQUOTES | ENT_XML1, 'UTF-8'));

        $keywords = $this->getKeywords();

        $replacements = array(
            '/\*/'                   => ' ',    // convert asterisks to spaces
            '/[\x{2000}-\x{200F}]/u' => '',     // remove zero width characters
            '/'.$keywords.'/i'       => '',     // remove keywords
            '/[\(\)]*/'              => '',     // remove parentheses
            '/[\-\/\.,"\' \!\?`]*$/'  => '',     // remove trailing punctuation and whitespace
            '/ {2,}/'                => ' ',    // correct more than 1 consecutive space
            '/([^\d])[,#](\d+)/'        => '\\1 \\2',    // correct comma
        );

        // replace these one by one as we rely on removing trailing stuff that doesn't necessarily end in exact order
        // eg: **- or *-* or --*, etc.
        foreach ($replacements as $pattern => $replacement) {
            $address = preg_replace($pattern, $replacement, $address);
        }

        // not sure how this works, but it seems to convert some funky
        // characters (at the moment of writing this we needed it for converting
        // subscript/superscript)
        $address = \Normalizer::normalize($address, \Normalizer::FORM_KD);

        // normalize swapped number-street
        if (preg_match('/^(\d{2,}) ([a-z ]+)$/i', $address, $matches)) {
            list (, $number, $street) = $matches;

            $address = sprintf('%s %d', $street, $number);
        }

        $address = strtr($address, $this->specialTokenMap);

        // normalize abbreviation
        $address = str_replace('str.', 'straat ', $address);

        return ucfirst(trim($address));
    }

    /**
     * Normalizes a house number
     *
     * @param  string|int $number
     * @param  string     $numberSuffix
     * @return string
     */
    protected function normalizeNumber($number, $numberSuffix)
    {
        $retval = array();

        foreach (array($number, $numberSuffix) as $part) {
            $subparts = preg_split('/[^\w\+#&]+/', $part);
            if ($subparts && count($subparts) > 0) {
                $retval = array_merge($retval, $subparts);
            } else {
                $retval[] = $part;
            }
        }

        // remove empty parts
        $retval = array_filter($retval, function($match) {
            return trim($match) !== '';
        });

        return empty($retval) ? null : implode('-', $retval);
    }

    /**
     * Process strippableKeywords and return as a regular expression
     *
     * @return string
     */
    protected function getKeywords()
    {
        if (!isset($this->keywords)) {
            $keywords = array_map(function ($keyword) {
                return preg_quote($keyword, '/');
            }, $this->strippableKeywords);

            $this->keywords = implode('|', $keywords);
        }

        return $this->keywords;
    }
}
