<?php

class AddressParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \TreeHouse\AddressParser\AddressParser
     */
    protected $parser;

    public function setUp()
    {
        $this->parser = new \TreeHouse\AddressParser\AddressParser();
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
        return array(
            array('Dorpsstraat 23-a', 'Dorpsstraat 23-a', 'Dorpsstraat', '23-a'),
            array('Dorpsstraat', 'Dorpsstraat', 'Dorpsstraat', null),
            array('Dorpsstraat/', 'Dorpsstraat', 'Dorpsstraat', null),
            array('Dorpsstraat -', 'Dorpsstraat', 'Dorpsstraat', null),
            array('Oranjeplein 40  E', 'Oranjeplein 40-E', 'Oranjeplein', '40-E'),
            array('De Ielreager 31 (*)', 'De Ielreager 31', 'De Ielreager', '31'),
            array('Delfland 5-**,', 'Delfland 5', 'Delfland', '5'),
            array('Fennewei 11*', 'Fennewei 11', 'Fennewei', '11'),
            array('Soestdijkseweg Zuid 87*A23', 'Soestdijkseweg Zuid 87-A23', 'Soestdijkseweg Zuid', '87-A23'),
            array('Laan van Rijnwijk 1*A7*', 'Laan van Rijnwijk 1-A7', 'Laan van Rijnwijk', '1-A7'),
            array('Neringpassage 306 (.)', 'Neringpassage 306', 'Neringpassage', '306'),
            array('Gillis van Ledenberchstraat 36--F*', 'Gillis van Ledenberchstraat 36-F', 'Gillis van Ledenberchstraat', '36-F'),
            array('Laan van Rijnwijk 1**A7', 'Laan van Rijnwijk 1-A7', 'Laan van Rijnwijk', '1-A7'),
            array('Tweede Kostverlorenkade 155 (III+IV)', 'Tweede Kostverlorenkade 155-III+IV', 'Tweede Kostverlorenkade', '155-III+IV'),
            array('Van Speykstraat 40 (B)', 'Van Speykstraat 40-B', 'Van Speykstraat', '40-B'),
            array('Bosruiterweg 25 (-15)', 'Bosruiterweg 25-15', 'Bosruiterweg', '25-15'),
            array('Vivaldiweg 5 ( )', 'Vivaldiweg 5', 'Vivaldiweg', '5'),
            array('Oosterend 61 (a-24 "Recreatie")', 'Oosterend 61-a-24', 'Oosterend', '61-a-24'),
            array('Deltageul25/ 27', 'Deltageul 25-27', 'Deltageul', '25-27'),
            array('1e Deltageul25/ 27', '1e Deltageul 25-27', '1e Deltageul', '25-27'),
            array('Rozenstraat 46 ¹L', 'Rozenstraat 46-1L', 'Rozenstraat', '46-1L'),
            array('Vogelenzangstraat 7 ²', 'Vogelenzangstraat 7-2', 'Vogelenzangstraat', '7-2'),
            array('Zoutmanstraat 74 part. az', 'Zoutmanstraat 74-part-az', 'Zoutmanstraat', '74-part-az'),
            array('Tuindorp 1 21.', 'Tuindorp 1-21', 'Tuindorp', '1-21'),
            array('Handellaan 131 *', 'Handellaan 131', 'Handellaan', '131'),
            array('Herengracht 67 ³', 'Herengracht 67-3', 'Herengracht', '67-3'),
            array('Palaceplein 7 23#M', 'Palaceplein 7-23#M', 'Palaceplein', '7-23#M'),
            array('Lagendijk 19 #M', 'Lagendijk 19-#M', 'Lagendijk', '19-#M'),
            array('Balistraat 103-3 & 4', 'Balistraat 103-3-&-4', 'Balistraat', '103-3-&-4'),
            array('De Ruyterstra&#8203;at', 'De Ruyterstraat', 'De Ruyterstraat', null),
            array('Floralaan 74..', 'Floralaan 74', 'Floralaan', '74'),
            array('Antwerpsestraat 102 -VERLAAGD!', 'Antwerpsestraat 102', 'Antwerpsestraat', '102'),

            array('25 witton street', 'Witton street 25', 'Witton street', '25'),
            array('34 Eerste Adjehstraat', 'Eerste Adjehstraat 34', 'Eerste Adjehstraat', '34'),
            array('Uhlandstr.10', 'Uhlandstraat 10', 'Uhlandstraat', '10'),
            array('Oltmansstraat,68T', 'Oltmansstraat 68-T', 'Oltmansstraat', '68-T'),
            array('rudolfarendsstraat#20B', 'Rudolfarendsstraat 20-B', 'Rudolfarendsstraat', '20-B'),
            array('pear road #25', 'Pear road 25', 'Pear road', '25'),
            array('boshuizerlaan 58`', 'Boshuizerlaan 58', 'Boshuizerlaan', '58'),
            array('\'t Leantsje 10', '\'t Leantsje 10', '\'t Leantsje', '10'),
            array('\'s-Gravendijkwal 95', '\'s-Gravendijkwal 95', '\'s-Gravendijkwal', '95'),
            array('\'s Gravenweg 754', '\'s Gravenweg 754', '\'s Gravenweg', '754'),

            // TODO this goes against everything... maybe maintain an index of exceptions?
            // we need more test data to determine this.
//             array('Plein 1953 83', 'Plein 1953 83', 'Plein 1953', '83')
//             array('Laan \'40-\'45 42', 'Laan \'40-\'45 42', 'Laan \'40-\'45', '42')
//            array('baronielaan ¨63a', 'Baronielaan 63-a', 'Baronielaan', '63-a'),
        );
    }

    /**
     * @dataProvider             getUnparseableAddresses
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Unabled to parse
     */
    public function testUnparseableAddress($address)
    {
        $result = $this->parser->parse($address);
    }

    public static function getUnparseableAddresses()
    {
        return array(
            array('Dr.C.A.Gerkestraat 20 De Schelp 81'),
            array('Dr.C.A.Gerkestr.20 De Schelp 81')
        );
    }
}
