<?php

namespace RiseTechApps\Address\Tests;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RiseTechApps\Address\Address;

class AddressPackageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Cria dinamicamente um model de teste usando a trait HasAddress
        if (!class_exists('Tests\Models\TestModel')) {
            eval('
            namespace Tests\Models;
            use Illuminate\Database\Eloquent\Model;
            use RiseTechApps\Address\Traits\HasAddress\HasAddress;use RiseTechApps\Address\Traits\HasAddress\HasAddressBilling;use RiseTechApps\Address\Traits\HasAddress\HasAddressDelivery;

            class TestModel extends Model {

                use HasAddress;
                use HasAddressDelivery;
                use HasAddressBilling;

                    public $incrementing = false;
                    protected $keyType = "string";


                protected $fillable = ["name", "id"];
            }
        ');
        }

        if(!\Schema::hasTable('test_models')){
            \Schema::create('test_models', function ($table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->timestamps();
            });
        }

    }

    public function test_it_creates_address_with_blank_model()
    {
        $testModelClass = 'Tests\Models\TestModel';

        $model = $testModelClass::create([
            'id' => Str::uuid(),
            'name' => 'Test Model Blank Address',
        ]);

        $this->assertDatabaseHas('addresses', [
            'address_id' => $model->id,
            'address_type' => $testModelClass,
            'zip_code' => null,
            'country' => null,
            'state' => null,
            'city' => null,
            'district' => null,
            'address' => null,
            'number' => null,
            'complement' => null,
        ]);
    }

    public function test_it_creates_address_with_default_model()
    {
        $testModelClass = 'Tests\Models\TestModel';

        Address::setAddress([
            'zip_code' => '12345-678',
            'country' => 'BRASIL',
            'state' => 'SP',
            'city' => 'CIDADE TESTE',
            'district' => 'BAIRRO TESTE',
            'address' => 'RUA TESTE',
            'number' => '100',
            'complement' => 'RUA TESTE PROLONGADA - DEFAULT',
        ]);

        $model = $testModelClass::create([
            'id' => Str::uuid(),
            'name' => 'Test Model Default Address',
        ]);

        $this->assertDatabaseHas('addresses', [
            'address_id' => $model->id,
            'address_type' => $testModelClass,
            'zip_code' => '12345-678',
            'country' => 'BRASIL',
            'state' => 'SP',
            'city' => 'CIDADE TESTE',
            'district' => 'BAIRRO TESTE',
            'address' => 'RUA TESTE',
            'number' => '100',
            'complement' => 'RUA TESTE PROLONGADA - DEFAULT',
            'type' => 'DEFAULT',
        ]);
    }

    public function test_it_creates_address_with_delivery_model()
    {
        $testModelClass = 'Tests\Models\TestModel';

        Address::setAddressDelivery([
            'zip_code' => '12345-678',
            'country' => 'BRASIL',
            'state' => 'SP',
            'city' => 'CIDADE TESTE',
            'district' => 'BAIRRO TESTE',
            'address' => 'RUA TESTE',
            'number' => '100',
            'complement' => 'RUA TESTE PROLONGADA - DELIVERY',
        ]);

        $model = $testModelClass::create([
            'id' => Str::uuid(),
            'name' => 'Test Model Delivery Address',
        ]);

        $this->assertDatabaseHas('addresses', [
            'address_id' => $model->id,
            'address_type' => $testModelClass,
            'zip_code' => '12345-678',
            'country' => 'BRASIL',
            'state' => 'SP',
            'city' => 'CIDADE TESTE',
            'district' => 'BAIRRO TESTE',
            'address' => 'RUA TESTE',
            'number' => '100',
            'complement' => 'RUA TESTE PROLONGADA - DELIVERY',
            'type' => 'DELIVERY',
        ]);
    }

    public function test_it_creates_address_with_billing_model()
    {
        $testModelClass = 'Tests\Models\TestModel';

        Address::setAddressBilling([
            'zip_code' => '12345-678',
            'country' => 'BRASIL',
            'state' => 'SP',
            'city' => 'CIDADE TESTE',
            'district' => 'BAIRRO TESTE',
            'address' => 'RUA TESTE',
            'number' => '100',
            'complement' => 'RUA TESTE PROLONGADA - BILLING',
        ]);

        $model = $testModelClass::create([
            'id' => Str::uuid(),
            'name' => 'Test Model Billing Address',
        ]);

        $this->assertDatabaseHas('addresses', [
            'address_id' => $model->id,
            'address_type' => $testModelClass,
            'zip_code' => '12345-678',
            'country' => 'BRASIL',
            'state' => 'SP',
            'city' => 'CIDADE TESTE',
            'district' => 'BAIRRO TESTE',
            'address' => 'RUA TESTE',
            'number' => '100',
            'complement' => 'RUA TESTE PROLONGADA - BILLING',
            'type' => 'BILLING',
        ]);
    }

    public function test_it_gets_zip_code_info_from_orchestrator()
    {
        $zip = '21720590';
        $response = Http::withHeaders([
            'X-API-KEY' => '8d65e959e02fb2dc060627da594f16c3be4232d950ef66d8a3c08473e0f81ecbd286a4d16038cfe24ff626932c0bb98539ae2f5813dfdec46c9c5ddd2ab369ac'
        ])->get("http://orchestrator.risetech.dev.br/api/v1/services/zip_code/{$zip}");

        $json = $response->json();

        $this->assertTrue($json['success']);

        $this->assertEquals([
            'zip_code'   => '21720-590',
            'address'    => 'Rua Aldir Pires',
            'number'     => '',
            'complement' => '',
            'district'   => 'Realengo',
            'country'    => 'BRA',
            'state'      => 'RJ',
            'city'       => 'Rio de Janeiro',
        ], $json['data']);
    }
}
