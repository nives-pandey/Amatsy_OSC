<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */

declare(strict_types=1);

namespace Amasty\Checkout\Test\Unit\Model\Optimization;

use Amasty\Checkout\Block\Onepage\LayoutWalkerFactory;
use Amasty\Checkout\Block\Onepage\LayoutWalker;
use Amasty\Checkout\Model\Optimization\LayoutJsDiffProcessor;

/**
 * @see LayoutJsDiffProcessor
 */
class LayoutJsDiffProcessorTest extends \PHPUnit\Framework\TestCase
{
    use \Amasty\Checkout\Test\Unit\Traits\ObjectManagerTrait;

    /**
     * @var LayoutJsDiffProcessor
     */
    private $processor;

    public function setUp(): void
    {
        $walkerFactory = $this->createPartialMock(LayoutWalkerFactory::class, ['create']);
        $walkerFactory->method('create')->willReturnCallback(
            function ($data) {
                return $this->getObjectManager()->getObject(LayoutWalker::class, $data);
            }
        );

        $this->processor = $this->getObjectManager()->getObject(
            LayoutJsDiffProcessor::class,
            ['layoutWalkerFactory' => $walkerFactory]
        );
    }

    /**
     * @covers LayoutJsDiffProcessor::createFlatDiff
     * @dataProvider layoutJsDataProvider
     */
    public function testCreateFlatDiff($originArray, $newArray, $expectedResult): void
    {
        $processedResult = $this->processor->createFlatDiff($originArray, $newArray);

        self::assertSame($expectedResult, $processedResult);
    }

    /**
     * @covers LayoutJsDiffProcessor::applyDiffToArray
     * @dataProvider layoutJsDataProvider
     */
    public function testApplyDiffToArray($originArray, $expectedResult, $diffArray): void
    {
        $processedResult = $this->processor->applyDiffToArray($originArray, $diffArray);

        self::assertSame($expectedResult, $processedResult);
    }

    public function layoutJsDataProvider(): array
    {
        return [
            'actionsTestCase' => [
                [
                    'lvl1' => [
                        'lvl2' => [],
                        'lvl2_mv1' => 'moved',
                        'lvl2_mv2' => 'moved',
                        'lvl2_mv3' => 'moved',
                        'lvl2_mv4' => 'moved',
                        'lvl2deleted' => 'should be removed',
                        'lvl2_mv5' => 'moved',
                        'lvl2_rp' => 'replaced',
                        'lvl2ch' => null,
                        'lvl2_1' => 'no changes',
                    ],
                    'lvl1_2' => [
                        'lvl2_rp2' => [
                            'lvl2_rp2_noch' => 'without Change',
                            'lvl2_rp2_ch' => 'withChange'
                        ],
                        'lvl2_2_not_replace' => 'delete me'
                    ]
                ],//origin
                [
                    'lvl1' => [
                        'lvl2' => ['lvl3' => ['lvl4str' => 'test']],
                        'lvl2ch' => 'new value and moved',
                        'lvl2_mv1' => 'moved',
                        'lvl2_mv4' => 'moved',
                        'lvl2_mv3' => 'moved',
                        'lvl2_mv5' => 'moved',
                        'lvl2_1' => 'no changes',
                        'lvl2add' => 'add',
                        'lvl2_mv2' => 'moved',
                        'lvl2_2_not_replace' => 'add',
                        'lvl2_rp2' => [
                            'lvl2_rp2_noch' => 'without Change',
                            'lvl2_rp2_ch' => 'with Change',
                            'lvl2_rp2_add' => 'with add'
                        ]
                    ],
                    'lvl1_2' => [
                        'lvl2_rp' => 'replaced',
                    ]
                ],//new
                [
                    'lvl1.lvl2.lvl3' => [
                        LayoutJsDiffProcessor::KEY_ACTION => LayoutJsDiffProcessor::ACTION_ADD,
                        LayoutJsDiffProcessor::KEY_VALUE => ['lvl4str' => 'test'],
                    ],
                    'lvl1.lvl2ch' => [
                        LayoutJsDiffProcessor::KEY_ACTION => LayoutJsDiffProcessor::ACTION_CHANGE,
                        LayoutJsDiffProcessor::KEY_VALUE => 'new value and moved',
                        LayoutJsDiffProcessor::KEY_MOVE_TO_POSITION => 0
                    ],
                    'lvl1.lvl2add' => [
                        LayoutJsDiffProcessor::KEY_ACTION => LayoutJsDiffProcessor::ACTION_ADD,
                        LayoutJsDiffProcessor::KEY_VALUE => 'add'
                    ],
                    'lvl1.lvl2_2_not_replace' => [
                        LayoutJsDiffProcessor::KEY_ACTION => LayoutJsDiffProcessor::ACTION_ADD,
                        LayoutJsDiffProcessor::KEY_VALUE => 'add',
                    ],
                    'lvl1.lvl2deleted' => [
                        LayoutJsDiffProcessor::KEY_ACTION => LayoutJsDiffProcessor::ACTION_REMOVE
                    ],
                    'lvl1.lvl2_rp' => [
                        LayoutJsDiffProcessor::KEY_ACTION => LayoutJsDiffProcessor::ACTION_REPLACE,
                        LayoutJsDiffProcessor::KEY_MOVE_TO_PATH => 'lvl1_2.lvl2_rp'
                    ],
                    'lvl1.lvl2_mv1' => [
                        LayoutJsDiffProcessor::KEY_MOVE_TO_POSITION => 2
                    ],
                    'lvl1.lvl2_mv2' => [
                        LayoutJsDiffProcessor::KEY_MOVE_TO_POSITION => 8
                    ],
                    'lvl1.lvl2_mv3' => [
                        LayoutJsDiffProcessor::KEY_MOVE_TO_POSITION => 4
                    ],
                    'lvl1.lvl2_mv4' => [
                        LayoutJsDiffProcessor::KEY_MOVE_TO_POSITION => 3
                    ],
                    'lvl1.lvl2_mv5' => [
                        LayoutJsDiffProcessor::KEY_MOVE_TO_POSITION => 5
                    ],
                    'lvl1_2.lvl2_rp2' => [
                        LayoutJsDiffProcessor::KEY_ACTION => LayoutJsDiffProcessor::ACTION_REPLACE,
                        LayoutJsDiffProcessor::KEY_MOVE_TO_PATH => 'lvl1.lvl2_rp2'
                    ],
                    'lvl1_2.lvl2_2_not_replace' => [
                        LayoutJsDiffProcessor::KEY_ACTION => LayoutJsDiffProcessor::ACTION_REMOVE,
                    ],
                    'lvl1.lvl2_rp2.lvl2_rp2_ch' => [
                        LayoutJsDiffProcessor::KEY_ACTION => LayoutJsDiffProcessor::ACTION_CHANGE,
                        LayoutJsDiffProcessor::KEY_VALUE => 'with Change',
                    ],
                    'lvl1.lvl2_rp2.lvl2_rp2_add' => [
                        LayoutJsDiffProcessor::KEY_ACTION => LayoutJsDiffProcessor::ACTION_ADD,
                        LayoutJsDiffProcessor::KEY_VALUE => 'with add',
                    ],
                ],//diff
            ],
            'moveTest' => [
                [
                    '_0' => '_2',
                    '_1' => '_0',
                    '_2' => '_3',
                    '_3' => '_1',
                    '_4' => '_6',
                    '_5' => '_4',
                    '_6' => '_5',
                ],
                [
                    '_1' => '_0',
                    '_3' => '_1',
                    '_0' => '_2',
                    '_2' => '_3',
                    '_5' => '_4',
                    '_6' => '_5',
                    '_4' => '_6',
                ],
                [
                    '_0' => [
                        LayoutJsDiffProcessor::KEY_MOVE_TO_POSITION => 2
                    ],
                    '_2' => [
                        LayoutJsDiffProcessor::KEY_MOVE_TO_POSITION => 3
                    ],
                    '_3' => [
                        LayoutJsDiffProcessor::KEY_MOVE_TO_POSITION => 0
                    ],
                    '_4' => [
                        LayoutJsDiffProcessor::KEY_MOVE_TO_POSITION => 6
                    ]
                ]//diff
            ],
            'pathShorterTestCase' => [
                [
                    'components' => [
                        'checkout' => [
                            'children' => [
                                'steps' => [
                                    'children' => [
                                        'shipping-step' => [
                                            'children' => [
                                                'step-config' => [
                                                    'children' => [
                                                        'shipping-rates-validation' => [
                                                            'children' => null
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],//origin
                [
                    'components' => [
                        'checkout' => [
                            'children' => [
                                'steps' => [
                                    'children' => [
                                        'shipping-step' => [
                                            'children' => [
                                                'step-config' => [
                                                    'children' => [
                                                        'shipping-rates-validation' => [
                                                            'children' => ['test']
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],//new
                [
                    '{SHIPPING_RATES_VALIDATION}.>>' => [
                        LayoutJsDiffProcessor::KEY_ACTION => LayoutJsDiffProcessor::ACTION_CHANGE,
                        LayoutJsDiffProcessor::KEY_VALUE => ['test']
                    ]
                ],//diff
            ],
            'valueDifferentTypesTestCase' => [
                [
                    'arrToNull' => [],
                    'nullToArr' => null,
                    'arrToString' => ['test'],
                    'zeroToString' => 0,
                    'stringToZero' => '0',
                    'deleteNull' => null
                ],//origin
                [
                    'arrToNull' => null,
                    'nullToArr' => [],
                    'arrToString' => 'test',
                    'zeroToString' => '0',
                    'stringToZero' => 0,
                    'addNull' => null
                ],//new
                [
                    'arrToNull' => [
                        LayoutJsDiffProcessor::KEY_ACTION => LayoutJsDiffProcessor::ACTION_CHANGE,
                        LayoutJsDiffProcessor::KEY_VALUE => null
                    ],
                    'nullToArr' => [
                        LayoutJsDiffProcessor::KEY_ACTION => LayoutJsDiffProcessor::ACTION_CHANGE,
                        LayoutJsDiffProcessor::KEY_VALUE => []
                    ],
                    'arrToString' => [
                        LayoutJsDiffProcessor::KEY_ACTION => LayoutJsDiffProcessor::ACTION_CHANGE,
                        LayoutJsDiffProcessor::KEY_VALUE => 'test'
                    ],
                    'zeroToString' => [
                        LayoutJsDiffProcessor::KEY_ACTION => LayoutJsDiffProcessor::ACTION_CHANGE,
                        LayoutJsDiffProcessor::KEY_VALUE => '0'
                    ],
                    'stringToZero' => [
                        LayoutJsDiffProcessor::KEY_ACTION => LayoutJsDiffProcessor::ACTION_CHANGE,
                        LayoutJsDiffProcessor::KEY_VALUE => 0
                    ],
                    'addNull' => [
                        LayoutJsDiffProcessor::KEY_ACTION => LayoutJsDiffProcessor::ACTION_ADD,
                        LayoutJsDiffProcessor::KEY_VALUE => null
                    ],
                    'deleteNull' => [
                        LayoutJsDiffProcessor::KEY_ACTION => LayoutJsDiffProcessor::ACTION_REMOVE,
                    ],
                ],//diff
            ],
        ];
    }
}
