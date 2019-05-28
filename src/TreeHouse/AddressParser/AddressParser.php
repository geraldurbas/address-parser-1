<?php

namespace TreeHouse\AddressParser;

use TreeHouse\AddressParser\Exception\InvalidAddressException;

class AddressParser
{
    /**
     * Start with potential numbers, followed by zero or one space and (at least one) alphabetic character(s),
     * and excluding any unexpected characters (#, @, %, etc.).
     *
     * @var string
     */
    protected $regexStreetName = '\d*[? a-zA-Z][^\d\$\%\@\#\*]';

    /**
     * The first following number(s) preceded by a letter, dash or space.
     *
     * @var string
     */
    protected $regexStreetNumber = '(?<=[a-z\- ])[0-9]';

    /**
     * Followed by pretty much everything else:
     * the special characters should've been stripped already at this point.
     *
     * @var string
     */
    protected $regexStreetNumberSuffix = '[a-z0-9\-\/\s\+,\.\(\)#&]';

    /**
     * Composed regex.
     *
     * @var string
     */
    protected $addressRegex;

    /**
     * Keysword that will be stripped during parsing.
     *
     * @var array
     */
    protected $strippableKeywords;

    /**
     * Maximum amount of alphanumeric characters that may occur in the street number suffix.
     *
     * @var integer
     */
    protected $maxSuffixWordLength;

    /**
     * @var array
     */
    protected $specialTokenMap = [
        '\'t'  => 'T_HET',
        '\'s'  => 'T_SCH',
        '\'s-' => 'T_SCH_DASH',
    ];

    /**
     * @param array   $strippableKeywords
     * @param integer $maxSuffixWordLength
     */
    public function __construct(array $strippableKeywords = [], $maxSuffixWordLength = 5)
    {
        $this->strippableKeywords  = $strippableKeywords;
        $this->maxSuffixWordLength = $maxSuffixWordLength;

        // compose full address regex
        $this->addressRegex = sprintf(
            '/^(?P<street>%s+)((?P<number>%s+)(?P<suffix>%s*))?$/i',
            $this->regexStreetName,
            $this->regexStreetNumber,
            $this->regexStreetNumberSuffix
        );
    }

    /**
     * Parses an address into parts.
     *
     * @param string $address
     *
     * @throws \RuntimeException when it is unable to parse the address
     *
     * @return array|null An array with the following keys: street, number,
     *                    address or null when an empty address is given
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

        $street = $matches['street'];
        $number = isset($matches['number']) ? $matches['number'] : null;
        $suffix = isset($matches['suffix']) ? $matches['suffix'] : null;

        // validate numberSuffix
        if (null !== $suffix && $this->maxSuffixWordLength > 0) {
            if (preg_match(sprintf('/[a-z]{%d,}/i', $this->maxSuffixWordLength), $suffix)) {
                throw new InvalidAddressException(
                    sprintf(
                        'Street number suffix has too many (>= %d) alphanumeric characters: "%s"',
                        $this->maxSuffixWordLength,
                        $suffix
                    )
                );
            }
        }

        $streetNumber = $this->normalizeNumber($number, $suffix);

        $street = strtr($street, array_flip($this->specialTokenMap));

        return [
            'street'                => trim($street),
            'number'                => $streetNumber,
            'number_without_suffix' => $number,
            'suffix'                => $suffix,
            'address'               => trim(sprintf('%s %s', $street, $streetNumber)),
        ];
    }

    /**
     * Parses an address.
     *
     * @param string $address
     *
     * @return string
     */
    protected function normalizeAddress($address)
    {
        // decode address to convert html entities ("&whatever;", "&#x1234;", etc)
        // to their unicode equivalents.
        $address = trim(html_entity_decode($address, ENT_NOQUOTES | ENT_XML1, 'UTF-8'));

        // remove keywords
        $address = str_ireplace($this->strippableKeywords, '', $address);

        $replacements = [
            '/\*/'                   => ' ',       // convert asterisks to spaces
            '/[\x{2000}-\x{200F}]/u' => '',        // remove zero width characters
            '/[\(\)]*/'              => '',        // remove parentheses
            '/[\-\/\.,"\' \!\?`]*$/' => '',        // remove trailing punctuation and whitespace
            '/ {2,}/'                => ' ',       // correct more than 1 consecutive space
            '/([^\d])[,#](\d+)/'     => '\\1 \\2', // correct comma
        ];

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
        if (preg_match('/^(?P<number>\d{2,}) (?P<street>[a-z ]+)$/i', $address, $matches)) {
            $address = sprintf('%s %d', $matches['street'], $matches['number']);
        }

        $address = strtr($address, $this->specialTokenMap);

        // normalize abbreviation
        $address = str_replace('str.', 'straat ', $address);

        return ucfirst(trim($address));
    }

    /**
     * Normalizes a street number.
     *
     * @param string|int $number
     * @param string     $numberSuffix
     *
     * @return string|null
     */
    protected function normalizeNumber($number, $numberSuffix)
    {
        $retval = [];

        foreach ([$number, $numberSuffix] as $part) {
            $subparts = preg_split('/[^\w\+#&]+/', $part);
            if (is_array($subparts) && !empty($subparts)) {
                $retval = array_merge($retval, $subparts);
            }
        }

        // remove empty parts
        $retval = array_filter($retval, function ($match) {
            return trim($match) !== '';
        });

        return empty($retval) ? null : implode('-', $retval);
    }
}
