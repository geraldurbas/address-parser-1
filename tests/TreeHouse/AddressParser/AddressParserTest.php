<?php

use TreeHouse\AddressParser\AddressParser;

class AddressParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddressParser
     */
    protected $parser;

    protected function setUp()
    {
        $this->parser = new AddressParser(['recreatie', 'verlaagd']);
    }

    /**
     * @dataProvider getAddresses
     */
    public function testAddressParsing($address, $parsedAddress, $parsedStreet, $parsedStreetNumber)
    {
        $parsed = $this->parser->parse($address);

        $this->assertSame($parsedAddress, $parsed['address'], sprintf('Expecting address "%s" to be parsed to address "%s", got "%s" instead', $address, $parsedAddress, $parsed['address']));
        $this->assertSame($parsedStreet, $parsed['street'], sprintf('Expecting address "%s" to be parsed to street "%s", got "%s" instead', $address, $parsedStreet, $parsed['street']));
        $this->assertSame($parsedStreetNumber, $parsed['number'], sprintf('Expecting address "%s" to be parsed to street number "%s", got "%s" instead', $address, $parsedStreetNumber, $parsed['number']));
    }

    public static function getAddresses()
    {
        return [
            ['Dorpsstraat 23-a', 'Dorpsstraat 23-a', 'Dorpsstraat', '23-a'],
            ['Dorpsstraat', 'Dorpsstraat', 'Dorpsstraat', null],
            ['Dorpsstraat/', 'Dorpsstraat', 'Dorpsstraat', null],
            ['Dorpsstraat -', 'Dorpsstraat', 'Dorpsstraat', null],
            ['Oranjeplein 40  E', 'Oranjeplein 40-E', 'Oranjeplein', '40-E'],
            ['De Ielreager 31 (*)', 'De Ielreager 31', 'De Ielreager', '31'],
            ['Delfland 5-**,', 'Delfland 5', 'Delfland', '5'],
            ['Fennewei 11*', 'Fennewei 11', 'Fennewei', '11'],
            ['Soestdijkseweg Zuid 87*A23', 'Soestdijkseweg Zuid 87-A23', 'Soestdijkseweg Zuid', '87-A23'],
            ['Laan van Rijnwijk 1*A7*', 'Laan van Rijnwijk 1-A7', 'Laan van Rijnwijk', '1-A7'],
            ['Neringpassage 306 (.)', 'Neringpassage 306', 'Neringpassage', '306'],
            ['Gillis van Ledenberchstraat 36--F*', 'Gillis van Ledenberchstraat 36-F', 'Gillis van Ledenberchstraat', '36-F'],
            ['Laan van Rijnwijk 1**A7', 'Laan van Rijnwijk 1-A7', 'Laan van Rijnwijk', '1-A7'],
            ['Tweede Kostverlorenkade 155 (III+IV)', 'Tweede Kostverlorenkade 155-III+IV', 'Tweede Kostverlorenkade', '155-III+IV'],
            ['Van Speykstraat 40 (B)', 'Van Speykstraat 40-B', 'Van Speykstraat', '40-B'],
            ['Bosruiterweg 25 (-15)', 'Bosruiterweg 25-15', 'Bosruiterweg', '25-15'],
            ['Vivaldiweg 5 ( )', 'Vivaldiweg 5', 'Vivaldiweg', '5'],
            ['Oosterend 61 (a-24 "Recreatie")', 'Oosterend 61-a-24', 'Oosterend', '61-a-24'],
            ['Deltageul25/ 27', 'Deltageul 25-27', 'Deltageul', '25-27'],
            ['1e Deltageul25/ 27', '1e Deltageul 25-27', '1e Deltageul', '25-27'],
            ['Rozenstraat 46 ¹L', 'Rozenstraat 46-1L', 'Rozenstraat', '46-1L'],
            ['Vogelenzangstraat 7 ²', 'Vogelenzangstraat 7-2', 'Vogelenzangstraat', '7-2'],
            ['Zoutmanstraat 74 part. az', 'Zoutmanstraat 74-part-az', 'Zoutmanstraat', '74-part-az'],
            ['Tuindorp 1 21.', 'Tuindorp 1-21', 'Tuindorp', '1-21'],
            ['Handellaan 131 *', 'Handellaan 131', 'Handellaan', '131'],
            ['Herengracht 67 ³', 'Herengracht 67-3', 'Herengracht', '67-3'],
            ['Palaceplein 7 23#M', 'Palaceplein 7-23#M', 'Palaceplein', '7-23#M'],
            ['Lagendijk 19 #M', 'Lagendijk 19-#M', 'Lagendijk', '19-#M'],
            ['Balistraat 103-3 & 4', 'Balistraat 103-3-&-4', 'Balistraat', '103-3-&-4'],
            ['De Ruyterstra&#8203;at', 'De Ruyterstraat', 'De Ruyterstraat', null],
            ['Floralaan 74..', 'Floralaan 74', 'Floralaan', '74'],
            ['Antwerpsestraat 102 -VERLAAGD!', 'Antwerpsestraat 102', 'Antwerpsestraat', '102'],
            ['29 oktoberplein 1', '29 oktoberplein 1', '29 oktoberplein', '1'],

            ['25 witton street', 'Witton street 25', 'Witton street', '25'],
            ['34 Eerste Adjehstraat', 'Eerste Adjehstraat 34', 'Eerste Adjehstraat', '34'],
            ['Uhlandstr.10', 'Uhlandstraat 10', 'Uhlandstraat', '10'],
            ['Oltmansstraat,68T', 'Oltmansstraat 68-T', 'Oltmansstraat', '68-T'],
            ['rudolfarendsstraat#20B', 'Rudolfarendsstraat 20-B', 'Rudolfarendsstraat', '20-B'],
            ['pear road #25', 'Pear road 25', 'Pear road', '25'],
            ['boshuizerlaan 58`', 'Boshuizerlaan 58', 'Boshuizerlaan', '58'],
            ['\'t Leantsje 10', '\'t Leantsje 10', '\'t Leantsje', '10'],
            ['\'s-Gravendijkwal 95', '\'s-Gravendijkwal 95', '\'s-Gravendijkwal', '95'],
            ['\'s Gravenweg 754', '\'s Gravenweg 754', '\'s Gravenweg', '754'],
//            ['baronielaan ¨63a', 'Baronielaan 63-a', 'Baronielaan', '63-a'],
        ];
    }

    /**
     * @dataProvider             getUnparseableAddresses
     * @expectedException        \TreeHouse\AddressParser\Exception\InvalidAddressException
     * @expectedExceptionMessage Unabled to parse
     */
    public function testUnparseableAddress($address)
    {
        $this->parser->parse($address);
    }

    public static function getUnparseableAddresses()
    {
        return [
            ['1235576'],
            ['Laan \'40-\'45 42'], // TODO this should be parseable
        ];
    }

    /**
     * @dataProvider             getUnparseableAddresses
     * @expectedException        \TreeHouse\AddressParser\Exception\InvalidAddressException
     * @expectedExceptionMessage too many (>= 3) alphanumeric characters
     */
    public function testTooManySuffixCharacters()
    {
        $parser = new AddressParser([], 10);
        $address = $parser->parse('Hoofdweg 5 bungalow 7');
        $this->assertSame('5-bungalow-7', $address['number']);

        $parser = new AddressParser([], 3);
        $parser->parse('Hoofdweg 5 bungalow 7');
    }

    public function testEmptyStringReturnsNull()
    {
        $this->assertNull($this->parser->parse(''));
        $this->assertNull($this->parser->parse(null));
        $this->assertNull($this->parser->parse(false));
        $this->assertNull($this->parser->parse(0));
    }
}
