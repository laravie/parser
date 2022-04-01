<?php

namespace Laravie\Parser\TestCase\Xml;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Laravie\Parser\Xml\Document;

class DocumentTest extends TestCase
{
    /**
     * Teardown the test environment.
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Test Laravie\Parser\Xml\Document::rebase() method.
     *
     * @test
     */
    public function it_can_rebase_document()
    {
        $expected = '<foo><bar>foobar</bar></foo>';

        $stub = new Document();

        $stub->setContent($expected);

        $result = $stub->rebase();

        $refl = new \ReflectionObject($stub);
        $content = $refl->getProperty('content');
        $content->setAccessible(true);

        $this->assertEquals($expected, $content->getValue($stub));
    }

    /**
     * Test Laravie\Parser\Xml\Document::namespaced() method.
     *
     * @test
     */
    public function it_can_read_namespaced_document()
    {
        $stub = new DocumentStub();

        $stub->setContent(simplexml_load_string(
            '<?xml version="1.0" standalone="yes"?>
                <people xmlns:p="http://example.org/ns" xmlns:t="http://example.org/test">
                    <p:person id="1">JohnDoe</p:person>
                    <p:person id="2">@Susie Q. Public</p:person>
                </people>'
        ));

        $result = $stub->namespaced('p', [], []);

        $this->assertCount(0, $result);
    }

    /**
     * Test Laravie\Parser\Xml\Document::setContent() method.
     *
     * @test
     */
    public function it_can_manually_set_content()
    {
        $expected = '<foo><bar>foobar</bar></foo>';

        $stub = new Document();

        $stub->setContent($expected);

        $refl = new \ReflectionObject($stub);
        $content = $refl->getProperty('content');
        $content->setAccessible(true);

        $this->assertEquals($expected, $content->getValue($stub));
    }

    /**
     * Test Laravie\Parser\Xml\Document::getContent() method.
     *
     * @test
     */
    public function it_can_get_content()
    {
        $expected = '<foo><bar>foobar</bar></foo>';

        $stub = new Document();

        $refl = new \ReflectionObject($stub);
        $content = $refl->getProperty('content');
        $content->setAccessible(true);

        $content->setValue($stub, $expected);

        $this->assertEquals($expected, $stub->getContent());
    }

    /**
     * Test Laravie\Parser\Xml\Document::parse() method.
     *
     * @test
     * @dataProvider dataCollectionProvider
     */
    public function it_can_parse_content($content, $schema, $expected)
    {
        $stub = new DocumentStub();

        $stub->setContent(simplexml_load_string($content));

        $data = $stub->parse($schema);

        $this->assertEquals($expected, $data);
    }

    /**
     * Test Laravie\Parser\Xml\Document::parse() method with tags.
     *
     * @test
     */
    public function it_can_parse_with_tag()
    {
        $expected = [
            'users' => [
                [
                    'id' => '1',
                    'fullname' => 'Mior Muhammad Zaki',
                ],
                [
                    'id' => '2',
                    'fullname' => 'Taylor Otwell',
                    'tag' => ['Laravel', 'PHP'],
                ],
            ],
        ];

        $stub = new DocumentStub();

        $stub->setContent(simplexml_load_string('<api>
    <user>
        <id>1</id>
        <name>Mior Muhammad Zaki</name>
    </user>
    <user>
        <id>2</id>
        <name>Taylor Otwell</name>
        <tag>Laravel</tag>
        <tag>PHP</tag>
    </user>
</api>'));

        $data = $stub->parse([
            'users' => ['uses' => 'user[id,name>fullname,tag(@=@)]'],
        ]);

        $this->assertEquals($expected, $data);
    }

    public function dataCollectionProvider()
    {
        yield [
'<api>
    <user followers="5">
        <id>1</id>
        <email type="primary">crynobone@gmail.com</email>
    </user>
</api>',
            [
                'id' => ['uses' => 'user.id'],
                'email' => ['uses' => 'user.email'],
                'followers' => ['uses' => 'user::followers'],
                'email_type' => ['uses' => 'user.email::type'],
            ],
            [
                'id' => 1,
                'email' => 'crynobone@gmail.com',
                'followers' => 5,
                'email_type' => 'primary',
            ],
        ];

        yield [
'<foo>
    <bar hello="hello world">foobar</bar>
    <world></world>
</foo>',
            [
                'foo' => ['uses' => 'bar', 'filter' => '@strToUpper'],
                'hello' => ['uses' => ['bar::hello', 'bar'], 'filter' => '@notFilterable'],
                'world' => ['uses' => 'world', 'default' => false],
                'foobar' => ['uses' => 'bar::foobar', 'default' => false],
                'username' => ['uses' => 'user::name', 'default' => 'Guest', 'filter' => '\Laravie\Parser\TestCase\Xml\FilterStub@filterStrToLower'],
                'google' => 'google.com',
                'facebook' => ['default' => 'facebook.com'],
            ],
            [
                'foo' => 'FOOBAR',
                'hello' => ['hello world', 'foobar'],
                'world' => false,
                'foobar' => false,
                'username' => 'guest',
                'google' => 'google.com',
                'facebook' => 'facebook.com',
            ],
        ];

        yield [
'<api>
    <collection>
        <user>
            <id>1</id>
            <name>Mior Muhammad Zaki</name>
        </user>
        <user>
            <id>2</id>
            <name>Taylor Otwell</name>
        </user>
    </collection>
</api>',
            [
                'users' => ['uses' => 'collection.user[id,name]'],
            ],
            [
                'users' => [
                    [
                        'id' => '1',
                        'name' => 'Mior Muhammad Zaki',
                    ],
                    [
                        'id' => '2',
                        'name' => 'Taylor Otwell',
                    ],
                ],
            ],
        ];

        yield [
'<api>
    <user>
        <id>1</id>
        <name>Mior Muhammad Zaki</name>
    </user>
    <user>
        <id>2</id>
        <name>Taylor Otwell</name>
    </user>
</api>',
            [
                'users' => ['uses' => 'user[id,name]'],
            ],
            [
                'users' => [
                    [
                        'id' => '1',
                        'name' => 'Mior Muhammad Zaki',
                    ],
                    [
                        'id' => '2',
                        'name' => 'Taylor Otwell',
                    ],
                ],
            ],
        ];

        yield [
'<api>
    <user>
        <id>1</id>
        <name>Mior Muhammad Zaki</name>
    </user>
    <user>
        <id>2</id>
        <name>Taylor Otwell</name>
    </user>
</api>',
            [
                'users' => ['uses' => 'user[id,name>fullname]'],
            ],
            [
                'users' => [
                    [
                        'id' => '1',
                        'fullname' => 'Mior Muhammad Zaki',
                    ],
                    [
                        'id' => '2',
                        'fullname' => 'Taylor Otwell',
                    ],
                ],
            ],
        ];

        yield [
'<api>
    <user>
        <property id="id">
            <value>1</value>
        </property>
        <property id="name">
            <value>Mior Muhammad Zaki</value>
        </property>
    </user>
    <user>
        <property id="id">
            <value>2</value>
        </property>
        <property id="name">
            <value>Taylor Otwell</value>
        </property>
    </user>
</api>',
            [
                'users' => ['uses' => 'user[property(::id=value)]'],
            ],
            [
                'users' => [
                    [
                        'id' => '1',
                        'name' => 'Mior Muhammad Zaki',
                    ],
                    [
                        'id' => '2',
                        'name' => 'Taylor Otwell',
                    ],
                ],
            ],
        ];

        yield [
'<api>
    <user>
        <property id="id">1</property>
        <property id="name">Mior Muhammad Zaki</property>
    </user>
    <user>
        <property id="id">2</property>
        <property id="name">Taylor Otwell</property>
    </user>
</api>',
            [
                'users' => ['uses' => 'user[property(::id=@)]'],
            ],
            [
                'users' => [
                    [
                        'id' => '1',
                        'name' => 'Mior Muhammad Zaki',
                    ],
                    [
                        'id' => '2',
                        'name' => 'Taylor Otwell',
                    ],
                ],
            ],
        ];

        yield [
'<api></api>',
            [
                'users' => ['uses' => 'user[id,name]', 'default' => null],
            ],
            [
                'users' => null,
            ],
        ];

        yield [
'<api><user></user></api>',
            [
                'users' => ['uses' => 'user[id,name]', 'default' => null],
            ],
            [
                'users' => [],
            ],
        ];

        yield [
'<products>
    <product ID="123456">
        <name>Lord of the Rings</name>
        <description>Just a book.</description>
        <properties>
            <property name="id">
                <value>2108</value>
            </property>
            <property name="avail">
                <value>1</value>
            </property>
            <property name="cat">
                <value>Fantasy Books</value>
            </property>
        </properties>
    </product>
    <product ID="123457">
        <name>Winnie The Pooh</name>
        <description>Good for children.</description>
        <properties>
            <property name="id">
                <value>3763</value>
            </property>
            <property name="avail">
                <value>0</value>
            </property>
            <property name="cat">
                <value>Child Books</value>
            </property>
        </properties>
    </product>
</products>',
            [
                'books' => ['uses' => 'product[::ID>id,name,properties.property(::name=value)>meta]', 'default' => null],
            ],
            [
                'books' => [
                    [
                        'id' => '123456',
                        'name' => 'Lord of the Rings',
                        'meta' => [
                            'id' => '2108',
                            'avail' => '1',
                            'cat' => 'Fantasy Books',
                        ],
                    ],
                    [
                        'id' => '123457',
                        'name' => 'Winnie The Pooh',
                        'meta' => [
                            'id' => '3763',
                            'avail' => '0',
                            'cat' => 'Child Books',
                        ],
                    ],
                ],
            ],
        ];

        yield [
'<products>
    <product ID="123456">
        <name>Lord of the Rings</name>
        <description>Just a book.</description>
        <properties>
            <property name="id">
                <value>2108</value>
            </property>
            <property name="avail">
                <value>1</value>
            </property>
            <property name="cat">
                <value>Fantasy Books</value>
            </property>
        </properties>
    </product>
    <product ID="123457">
        <name>Winnie The Pooh</name>
        <description>Good for children.</description>
        <properties>
            <property name="id">
                <value>3763</value>
            </property>
            <property name="avail">
                <value>0</value>
            </property>
            <property name="cat">
                <value>Child Books</value>
            </property>
        </properties>
    </product>
</products>',
            [
                'books' => ['uses' => 'product[::ID>bookID,name,properties.property(::name=value)]', 'default' => null],
            ],
            [
                'books' => [
                    [
                        'bookID' => '123456',
                        'name' => 'Lord of the Rings',
                        'id' => '2108',
                        'avail' => '1',
                        'cat' => 'Fantasy Books',
                    ],
                    [
                        'bookID' => '123457',
                        'name' => 'Winnie The Pooh',
                        'id' => '3763',
                        'avail' => '0',
                        'cat' => 'Child Books',
                    ],
                ],
            ],
        ];

        yield [
'<api>
    <Country name="Albania" id="ALB">
        <Competition id="ALB_1" name="Albania 1" event_name="Super League" sport="soccer" levels_on_pyramid="0" competition_type="league" image="" timestamp="0"/>
    </Country>
    <Country name="Algeria" id="ALG">
        <Competition id="ALG_1" name="Algeria 1" event_name="Ligue 1" sport="soccer" levels_on_pyramid="0" competition_type="league" image="" timestamp="0"/>
    </Country>
</api>',
            [
                'data' => ['uses' => 'Country[Competition::id>id,Competition::name>name,Competition::event_name>event_name]', 'default' => null],
            ],
            [
                'data' => [
                    [
                        'id' => 'ALB_1',
                        'name' => 'Albania 1',
                        'event_name' => 'Super League',
                    ],
                    [
                        'id' => 'ALG_1',
                        'name' => 'Algeria 1',
                        'event_name' => 'Ligue 1',
                    ],
                ],
            ],
        ];

        yield [
'<xml time="1460026675">
    <Country id="ALG" name="Algeria" image="Algeria.png" lastupdate="1315773004"/>
    <Country id="ASM" name="American Samoa" image="American-Samoa.png" lastupdate="1315773004"/>
    <Country id="AND" name="Andorra" image="Andorra.png" lastupdate="1315773004"/>
</xml>',
            [
                'countries' => ['uses' => 'Country[::id>id,::name>name]', 'default' => null],
            ],
            [
                'countries' => [
                    [
                        'id' => 'ALG',
                        'name' => 'Algeria',
                    ],
                    [
                        'id' => 'ASM',
                        'name' => 'American Samoa',
                    ],
                    [
                        'id' => 'AND',
                        'name' => 'Andorra',
                    ],
                ],
            ],
        ];

        yield [
'<course code="ABC">
    <title lang="sv">Utmattning</title>
    <title lang="en">Fatigue</title>
</course>',
            [
                'code' => ['uses' => '::code'],
                'title' => ['uses' => 'title[::lang>locale,@>name]'],
            ],
            [
                'code' => 'ABC',
                'title' => [
                    ['locale' => 'sv', 'name' => 'Utmattning'],
                    ['locale' => 'en', 'name' => 'Fatigue'],
                ],
            ],
        ];
    }

    /** @test */
    public function testParseValueCollectionMultiLevels()
    {
        $expected = [
            'product' => [
                [
                    'node_id' => '98b9b3498bd0',
                    'product_features' => [
                        [
                            'feature' => [
                                [
                                    'name' => 'Symbol Info',
                                    'value' => 'Angabe der Bemessungstemperatur nach IEC. Dies ist die höchste Temperatur, für welche die Fassung konstruiert wurde.  Eventuell ist eine zusätzliche Information für die Temperatur an der Fassungsrückseite angegeben (z. B. Tm 110° C).',
                                    'feature_translation_name' => 'Symbol Info',
                                    'feature_translation_value' => 'The maximum operating temperature is given by a T marking according to IEC. This is the maximum continuous operating temperature for which the lampholder is designed.  Additional information may be given for the rear of the lampholder (i.e.. Tm 110° C). For UL temperature marks, contact BJB.',
                                ],
                                [
                                    'name' => 'Symbol Ausprägung',
                                    'value' => '0,4 - 1,5',
                                    'feature_translation_name' => 'Symbol value',
                                    'feature_translation_value' => '0.4 - 1.5',
                                ],
                                [
                                    'name' => 'Symbol Text',
                                    'value' => 'Leuchtwanddicke mit Angabe in mm',
                                    'feature_translation_name' => 'Symbol Text',
                                    'feature_translation_value' => 'Mounting material thickness (in mm)',
                                ],
                            ],
                        ],
                    ],
                    'mime_info' => [
                        [
                            'mime' => [
                                [
                                    'id' => '1',
                                    'caption' => 'Web_Foto_Standard_1',
                                    'mime_type' => 'image/jpeg',
                                    'mime_source' => 'images\\Web_Foto_Standard_1\\47_319_2224.png',
                                    'mime_description' => 'Web Foto Standard 1',
                                ],
                                [
                                    'id' => '2',
                                    'caption' => 'Web_Zeichnung_Standard_1',
                                    'mime_type' => 'image/jpeg',
                                    'mime_source' => 'images\\Web_Zeichnung_Standard_1\\test05.gif',
                                    'mime_description' => 'Web Zeichnung Standard 1',
                                ],
                                [
                                    'id' => '3',
                                    'caption' => 'Zusatzinformation_1',
                                    'mime_type' => 'application/pdf',
                                    'mime_source' => 'images\\Zusatzinformation_1\\Informationen.pdf',
                                    'mime_description' => 'Zusatzinformation 1',
                                ],
                                [
                                    'id' => '4',
                                    'caption' => 'CAD3D',
                                    'mime_type' => 'image/jpeg',
                                    'mime_source' => 'CAD3D\\01TYA5YY04X_T.ZIP',
                                    'mime_description' => null,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $stub = new DocumentStub();

        $stub->setContent(simplexml_load_string('<api>
<T_NEW_CATALOG>
    <PRODUCT mode="new">
      <NODE_ID>98b9b3498bd0</NODE_ID>
      <PRODUCT_FEATURES>
        <FEATURE>
          <FNAME>Symbol Info</FNAME>
          <FVALUE>Angabe der Bemessungstemperatur nach IEC. Dies ist die höchste Temperatur, für welche die Fassung konstruiert wurde.  Eventuell ist eine zusätzliche Information für die Temperatur an der Fassungsrückseite angegeben (z. B. Tm 110° C).</FVALUE>
          <TRANSLATION_US>
            <FNAME>Symbol Info</FNAME>
            <FVALUE>The maximum operating temperature is given by a T marking according to IEC. This is the maximum continuous operating temperature for which the lampholder is designed.  Additional information may be given for the rear of the lampholder (i.e.. Tm 110° C). For UL temperature marks, contact BJB.</FVALUE>
          </TRANSLATION_US>
        </FEATURE>
        <FEATURE>
          <FNAME>Symbol Ausprägung</FNAME>
          <FVALUE>0,4 - 1,5</FVALUE>
          <TRANSLATION_US>
            <FNAME>Symbol value</FNAME>
            <FVALUE>0.4 - 1.5</FVALUE>
          </TRANSLATION_US>
        </FEATURE>
        <FEATURE>
          <FNAME>Symbol Text</FNAME>
          <FVALUE>Leuchtwanddicke mit Angabe in mm</FVALUE>
          <TRANSLATION_US>
            <FNAME>Symbol Text</FNAME>
            <FVALUE>Mounting material thickness (in mm)</FVALUE>
          </TRANSLATION_US>
        </FEATURE>
      </PRODUCT_FEATURES>
      <MIME_INFO>
        <MIME>
          <property id="id">1</property>
          <property id="caption">Web_Foto_Standard_1</property>
          <MIME_TYPE>image/jpeg</MIME_TYPE>
          <MIME_SOURCE>images\Web_Foto_Standard_1\47_319_2224.png</MIME_SOURCE>
          <MIME_DESCRIPTION>Web Foto Standard 1</MIME_DESCRIPTION>
        </MIME>
        <MIME>
          <property id="id">2</property>
          <property id="caption">Web_Zeichnung_Standard_1</property>
          <MIME_TYPE>image/jpeg</MIME_TYPE>
          <MIME_SOURCE>images\Web_Zeichnung_Standard_1\test05.gif</MIME_SOURCE>
          <MIME_DESCRIPTION>Web Zeichnung Standard 1</MIME_DESCRIPTION>
        </MIME>
        <MIME>
          <property id="id">3</property>
          <property id="caption">Zusatzinformation_1</property>
          <MIME_TYPE>application/pdf</MIME_TYPE>
          <MIME_SOURCE>images\Zusatzinformation_1\Informationen.pdf</MIME_SOURCE>
          <MIME_DESCRIPTION>Zusatzinformation 1</MIME_DESCRIPTION>
        </MIME>
        <MIME>
          <property id="id">4</property>
          <property id="caption">CAD3D</property>
          <MIME_TYPE>image/jpeg</MIME_TYPE>
          <MIME_SOURCE>CAD3D\01TYA5YY04X_T.ZIP</MIME_SOURCE>
          <MIME_DESCRIPTION></MIME_DESCRIPTION>
        </MIME>
      </MIME_INFO>
    </PRODUCT>
</T_NEW_CATALOG>
</api>'));

        $data = $stub->parse([
            'product' => ['uses' => 'T_NEW_CATALOG.PRODUCT[NODE_ID>node_id,PRODUCT_FEATURES{FEATURE{FNAME>name,FVALUE>value,TRANSLATION_US.FNAME>feature_translation_name,TRANSLATION_US.FVALUE>feature_translation_value}>feature}>product_features,MIME_INFO{MIME{property(::id=@),MIME_TYPE>mime_type,MIME_SOURCE>mime_source,MIME_DESCRIPTION>mime_description}>mime}>mime_info]'],
        ]);

        $this->assertEquals($expected, $data);
    }
    
     /** @test */
    public function testParseValueCollectionMultiLevelsPartTwo()
    {
        $expected = [
            'errors' => [
                'ErrorItem' => [
                    'DataArea' => 'AREA1',
                    'DynPar' => 'T0019:1,R0109:,R0108:,R0107:,T0025:X',
                    'StatusItem' => 'REJECTED',
                    'Error' => [
                        'Code' => 'ERR_2059',
                        'Severity' => 'Critical',
                        'Text' => 'Error message ERR_2059.',
                        'Localization' => [
                            'DataAreaCode' => 'AREA1_CODE',
                            'Detail' =>[
                                'DynPar' => 'T0019:1,R0109:test1,R0108:,R0107:,T0025:X'
                            ]
                        ]
                    ]
                ],[
                    'DataArea' => 'AREA2',
                    'DynPar' => 'T0019:2,R0109:test2,R0108:,R0107:,T0025:X',
                    'StatusItem' => 'REJECTED',
                    'Error' => [
                        'Code' => 'ERR_2060',
                        'Severity' => 'Warning',
                        'Text' => 'Error message ERR_2060.',
                        'Localization' => [
                            'DataAreaCode' => 'AREA2_CODE',
                            'Detail' => [
                                'DynPar' => 'T0019:2,R0109:test2,R0108:,R0107:,T0025:X'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $stub = new DocumentStub();

        $stub->setContent(simplexml_load_string("<StepProcess>
		<StepName>FORMAT_CONTROL</StepName>
		<Result>NOT_ACCEPTED</Result>
		<ErrorsProcess>
			<ErrorItem>
				<DataArea>AREA1</DataArea>
				<DynPar>T0019:1,R0109:,R0108:,R0107:,T0025:X</DynPar>
				<StatusItem>REJECTED</StatusItem>
				<Error>
					<Code>ERR_2059</Code>
					<Severity>Critical</Severity>
					<Text>Error message ERR_2059.</Text>
					<Localization>
						<DataAreaCode>AREA1_CODE</DataAreaCode>
						<Detail>
							<DynPar>T0019:1,R0109:test1,R0108:,R0107:,T0025:X</DynPar>
						</Detail>
					</Localization>
				</Error>
			</ErrorItem>
			<ErrorItem>
				<DataArea>AREA2</DataArea>
				<DynPar>T0019:2,R0109:test2,R0108:,R0107:,T0025:X</DynPar>
				<StatusItem>REJECTED</StatusItem>
				<Error>
					<Code>ERR_2060</Code>
					<Severity>Warning</Severity>
					<Text>Error message ERR_2060.</Text>
					<Localization>
						<DataAreaCode>AREA2_CODE</DataAreaCode>
						<Detail>
							<DynPar>T0019:2,R0109:test2,R0108:,R0107:,T0025:X</DynPar>
						</Detail>
					</Localization>
				</Error>
			</ErrorItem>
		</ErrorsProcess>
	</StepProcess>
<StepProcess>"));

        $data = $stub->parse([
            'errors' => ['uses' => 'ErrorsProcess[ErrorItem{DataArea,DynPar,StatusItem,Error{Code,Severity,Text,Localization{DataAreaCode,Detail.DynPar}}}]'],
        ]);

        $this->assertEquals($expected, $data);
    }
}

class DocumentStub extends \Laravie\Parser\Xml\Document
{
    public function filterStrToUpper($value)
    {
        return strtoupper($value);
    }
}

class FilterStub
{
    public function filterStrToLower($value)
    {
        return strtolower($value);
    }
}
