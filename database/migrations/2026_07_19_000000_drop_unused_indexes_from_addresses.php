<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Remove índices ociosos da tabela `addresses`.
 *
 * MOTIVO: o planner do PostgreSQL avalia TODOS os índices da tabela ao planejar
 * cada query. Com 8 índices sem uso (idx_scan = 0 em pg_stat_user_indexes), o
 * PLANEJAMENTO de uma simples busca por endereço custava ~11ms (220 buffers de
 * catálogo) — enquanto a EXECUÇÃO leva 0.06ms. Ou seja, os 14-26ms observados nas
 * requests eram tempo de planning, não de busca.
 *
 * O único índice que serve o caminho quente é o composto
 * (address_type, address_id, type, is_default) — mantido. A PK também.
 *
 * O `(address_type, address_id)` (uuidMorphs) é PREFIXO exato do composto →
 * 100% redundante. Os demais (zip_code/country/state/city/district/usage_count/
 * last_used_at) só cobrem scopes (byState/byCity/mostUsed/...) que não são usados
 * no hot path. Se você passar a filtrar por essas colunas em produção, recrie o
 * índice específico.
 *
 * DROP CONCURRENTLY (fora de transação) para não travar escritas na tabela.
 */
return new class extends Migration {
    public $withinTransaction = false;

    /** Índices ociosos a remover (idx_scan = 0). */
    private array $indexes = [
        'addresses_address_type_address_id_index', // redundante com o composto
        'addresses_zip_code_index',
        'addresses_country_index',
        'addresses_state_index',
        'addresses_city_index',
        'addresses_district_index',
        'addresses_usage_count_index',
        'addresses_last_used_at_index',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->indexes as $index) {
            DB::statement("DROP INDEX CONCURRENTLY IF EXISTS \"{$index}\"");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // Recria os índices removidos (mesma definição original).
        $recreate = [
            'addresses_address_type_address_id_index' => 'address_type, address_id',
            'addresses_zip_code_index'                => 'zip_code',
            'addresses_country_index'                 => 'country',
            'addresses_state_index'                   => 'state',
            'addresses_city_index'                    => 'city',
            'addresses_district_index'                => 'district',
            'addresses_usage_count_index'             => 'usage_count',
            'addresses_last_used_at_index'            => 'last_used_at',
        ];

        foreach ($recreate as $name => $columns) {
            DB::statement("CREATE INDEX CONCURRENTLY IF NOT EXISTS \"{$name}\" ON addresses ({$columns})");
        }
    }
};
