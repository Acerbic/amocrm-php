<?php

class LeadMock extends \AmoCRM\Models\Lead
{
    protected function getRequest($url, $parameters = [], $modified = null)
    {
        return ['leads' => []];
    }

    protected function postRequest($url, $parameters = [])
    {
        return [
            'leads' => [
                'add' => [
                    ['id' => 100],
                    ['id' => 200]
                ],
                'update' => [
                    ['id' => 100],
                    ['id' => 200]
                ]
            ]
        ];
    }
}

class LeadTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var null|LeadMock
     */
    private $model = null;

    public function setUp()
    {
        $paramsBag = new \AmoCRM\Request\ParamsBag();
        $this->model = new LeadMock($paramsBag);
    }

    /**
     * @dataProvider fieldsProvider
     */
    public function testFields($field, $value, $expected)
    {
        $this->model[$field] = $value;

        $this->assertEquals($this->model[$field], $expected);
    }

    public function testCustomFields()
    {
        $this->model->addCustomField(100, 'Custom text');
        $this->model->addCustomField(200, 'test@mail.com', 'WORK');
        $this->model->addCustomField(300, [
            ['415.874.3275', 'MOB'],
            ['415.374.3278', 'OTHER'],
            ['415.374.3279', 'FAX'],
        ]);

        $this->assertArrayHasKey('id', $this->model['custom_fields'][0]);
        $this->assertArrayHasKey('values', $this->model['custom_fields'][0]);
        $this->assertArrayHasKey('value', $this->model['custom_fields'][0]['values'][0]);
        $this->assertEquals('Custom text', $this->model['custom_fields'][0]['values'][0]['value']);

        $this->assertArrayHasKey('id', $this->model['custom_fields'][1]);
        $this->assertArrayHasKey('values', $this->model['custom_fields'][1]);
        $this->assertArrayHasKey('value', $this->model['custom_fields'][1]['values'][0]);
        $this->assertEquals('test@mail.com', $this->model['custom_fields'][1]['values'][0]['value']);
        $this->assertEquals('WORK', $this->model['custom_fields'][1]['values'][0]['enum']);

        $this->assertArrayHasKey('id', $this->model['custom_fields'][2]);
        $this->assertArrayHasKey('values', $this->model['custom_fields'][2]);
        $this->assertCount(3, $this->model['custom_fields'][2]['values']);
    }

    public function testApiList()
    {
        $result = $this->model->apiList([
            'query' => 'test',
        ]);

        $this->assertEquals([], $result);
    }

    public function testApiAdd()
    {
        $this->model['name'] = 'Тестовая сделка';

        $this->assertEquals(100, $this->model->apiAdd());
        $this->assertCount(2, $this->model->apiAdd([$this->model, $this->model]));
    }

    public function testApiUpdate()
    {
        $this->model['name'] = 'Тестовая сделка';

        $this->assertTrue($this->model->apiUpdate(1));
        $this->assertTrue($this->model->apiUpdate(1, 'now'));
    }

    public function fieldsProvider()
    {
        return [
            // field, value, expected
            ['name', 'Сделка', 'Сделка'],
            ['date_create', '2016-04-01 00:00:00', strtotime('2016-04-01 00:00:00')],
            ['last_modified', '2016-04-01 00:00:00', strtotime('2016-04-01 00:00:00')],
            ['status_id', 100, 100],
            ['price', 300000, 300000],
            ['responsible_user_id', 100, 100],
            ['request_id', 100, 100],
            ['tags', 'Tag', 'Tag'],
            ['tags', ['Tag 1', 'Tag 2'], 'Tag 1,Tag 2'],
            ['visitor_uid', '12345678-52d2-44c2-9e16-ba0052d9f6d6', '12345678-52d2-44c2-9e16-ba0052d9f6d6']
        ];
    }
}
