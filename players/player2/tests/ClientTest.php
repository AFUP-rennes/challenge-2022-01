<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Warship\Client;

final class ClientTest extends TestCase
{
    protected Client $client;

    /**
     * @dataProvider coordsProvider
     */
    public function testCoords($x, $y, $coord): void
    {
        $clientCoord = Client::getCoord($x, $y);
        $this->assertEquals($coord, $clientCoord);
    }

    /**
     * @dataProvider coordsProvider
     */
    public function testPositions($x, $y, $coord): void
    {
        $clientPosition = Client::getPosition($coord);
        $this->assertEquals($x, $clientPosition['x']);
        $this->assertEquals($y, $clientPosition['y']);
    }

    /**
     * @dataProvider playProvider
     */
    public function testPlacement(array $plays)
    {
        foreach($plays as $play) {
            $client = new Client();
            
            foreach($play['my'] as $boat) {
                [$x, $y, $length, $isHorizontal, $coords] = $boat;
                $client->placeBoat($x, $y, $length, $isHorizontal);
                
                $board = $client->getBoard();
                foreach($coords as $coord) {
                    $this->assertArrayHasKey($coord, $board);
                    $this->assertEquals(Client::BOARD_BOAT, $board[$coord], $coord);
                }
            }
        }
    }

    /**
     * @dataProvider playProvider
     */
    public function testPlays(array $plays)
    {
        foreach($plays as $play) {
            $myClient = new Client();
            $ennemyClient = new Client();
            
            foreach($play['my'] as $boat) {
                [$x, $y, $length, $isHorizontal] = $boat;
                $myClient->placeBoat($x, $y, $length, $isHorizontal);
            }
            foreach($play['ennemy'] as $boat) {
                [$x, $y, $length, $isHorizontal] = $boat;
                $ennemyClient->placeBoat($x, $y, $length, $isHorizontal);
            }

            foreach($play['turns'] as $i => $turn) {
                $fromClient = $i % 2 === 0 ? $myClient : $ennemyClient;
                $toClient = $i % 2 === 0 ? $ennemyClient : $myClient;

                [$coord, $expected] = $turn;
                $fromClient->shot($coord);
                $response = $toClient->ennemyShot($coord);
                $this->assertEquals($expected, $response);
                $fromClient->shotResponse($expected === 'miss' ? Client::BOARD_WATER : Client::BOARD_BOAT);
            }
        }
    }

    public function playProvider()
    {
        return [
            [
                [
                    [
                        'my' => [
                            [0, 0, 2, true, ['A1', 'A2']],
                            [2, 3, 3, false, ['C4', 'D4', 'E4']]
                        ],
                        'ennemy' => [
                            [1, 1, 2, true, ['B2', 'B3']]
                        ],
                        'turns' => [
                            ['B2', 'hit'],
                            ['A3', 'miss'],
                            ['B3', 'won'],
                        ]
                    ]
                ]
            ]
        ];
    }

    public function coordsProvider()
    {
        return [
            [0, 0, 'A1'],
            [0, 4, 'A5'],
            [3, 5, 'D6'],
            [4, 8, 'E9'],
            [5, 9, 'F10'],
            [9, 0, 'J1'],
        ];
    }
}
