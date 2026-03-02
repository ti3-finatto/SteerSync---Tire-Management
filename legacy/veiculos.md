# TASK: Cadastro de Veículos — SteeerSync

## Contexto Geral

Este documento instrui a IA a desenvolver o módulo de **Cadastro de Veículos** no sistema SteeerSync, seguindo rigorosamente os padrões encontrados nos arquivos da pasta `legacy/` na raiz do projeto Laravel.

Antes de escrever qualquer código:
1. Leia os arquivos em `legacy/` para entender padrões de nomenclatura, estrutura de controllers, views, models e migrations.
2. Siga o mesmo estilo de código, convenções de nomes e estrutura de pastas já existentes no projeto.
3. Não invente estruturas: espelhe o que já existe.

---

## Banco de Dados — Estrutura de Referência

O sistema legado usa as tabelas abaixo. A migration para `vehicles` (equivalente a `t_veiculo`) **já existe**. As demais tabelas de apoio também já devem existir. Confirme antes de criar qualquer migration nova.

### Tabelas do legado e seus equivalentes no SteeerSync

| Tabela Legado             | Equivalente SteeerSync        | Observação                                      |
|---------------------------|-------------------------------|-------------------------------------------------|
| `t_veiculo`               | `vehicles`                    | Migration já criada                             |
| `t_marcaveiculo`          | `vehicle_brands`              | Cadastro simples                                |
| `t_modeloveiculo`         | `vehicle_models`              | Vínculo com marca e tipo                        |
| `t_veiculoconfiguracao`   | `vehicle_configurations`      | Configuração/layout de eixos do veículo         |
| `t_posicao`               | `tire_positions`              | Posições de pneu (ex: Dianteiro Direito)        |
| `t_posicaoxconfiguracao`  | `configuration_positions`     | Relacionamento configuração ↔ posição + eixo    |
| `t_eixos`                 | `axles`                       | Eixos por tipo de veículo (CV/CR)               |
| `t_pneuatual`             | `current_tires`               | Pneu atual por posição no veículo               |

### Campos da tabela `vehicles` (migration já existente)

```
id                      (PK, auto increment)
plate          varchar  (placa — obrigatória se método = placa)
chassis        varchar  (chassi — obrigatório se método = chassi)
fleet_number   varchar  (frota, alfanumérico)
unit_id        FK       (unidade)
brand_id       FK       (marca — via model)
model_id       FK       (modelo)
configuration_id FK     (configuração de eixos/posições)
recommended_pressure int (calibragem recomendada)
status         varchar  (A = Ativo, I = Inativo)
odometer_type  varchar  (S = Sim, N = Não — controla uso de odômetro)
km             int
notes          text
created_by     FK
created_at
updated_at
```

---

## Regras de Negócio — Cadastro do Veículo

### 1. Método de Identificação (Placa ou Chassi)

- O formulário deve ter um **toggle/checkbox** no topo: `Cadastrar por Placa` ou `Cadastrar por Chassi`.
- Se **Placa** for selecionada:
  - Campo `Placa` aparece e é **obrigatório**.
  - Campo `Chassi` permanece visível, mas **opcional**.
- Se **Chassi** for selecionada:
  - Campo `Placa` **não aparece** no formulário.
  - Campo `Chassi` é **obrigatório**.
- Essa lógica deve ser controlada via JavaScript no front-end (sem recarregar a página) e validada também no back-end (FormRequest).

### 2. Campos do Formulário

```
[ ] Cadastrar por Chassi  ← toggle (default: Placa)

Placa*         (visível apenas no modo Placa)
Chassi         (visível sempre; obrigatório no modo Chassi)
Frota          (alfanumérico, livre)
Unidade*       (select — FK units)
Marca*         (select — FK vehicle_brands)
Modelo*        (select dependente da Marca — FK vehicle_models, filtrado por brand_id)
Configuração*  (select — FK vehicle_configurations, filtrado por tipo do modelo: CV ou CR)
Calibragem Recomendada (inteiro, ex: 105 ou 120)
```

### 3. Seleção Dependente de Campos

- Ao selecionar a **Marca**, o select de **Modelo** deve ser recarregado via AJAX com os modelos daquela marca.
- Ao selecionar o **Modelo**, o select de **Configuração** deve ser recarregado via AJAX filtrando pelo tipo (`CV` = veículo motor, `CR` = carreta/reboque) do modelo selecionado.

### 4. Visualização Dinâmica de Posições (mapa de pneus)

Esta é a funcionalidade central diferenciada. O sistema legado usava **imagens fixas** por configuração. No SteeerSync, as posições devem ser **geradas dinamicamente** a partir dos dados da tabela `configuration_positions` (equivalente a `t_posicaoxconfiguracao`).

**Como funciona a tabela `configuration_positions`:**

```sql
-- Exemplo para configuração 4 (6x2 B — D/2, T/4, L/4)
-- PSCF_CODIGO | VEIC_CODIGO | POS_CODIGO | PSCF_PAR | PSCF_EIXO
--      9      |      4      |     1      |    2     |    1       ← Dianteiro Direito, par com pos 2, eixo 1
--     10      |      4      |     2      |    1     |    1       ← Dianteiro Esquerdo, par com pos 1, eixo 1
--     11      |      4      |    14      |   15     |    2       ← Tração Direito Externo
--     12      |      4      |    15      |   14     |    2       ← Tração Direito Interno
--     13      |      4      |    16      |   17     |    2       ← Tração Esquerdo Externo
--     14      |      4      |    17      |   16     |    2       ← Tração Esquerdo Interno
--     15      |      4      |    18      |   19     |    3       ← Truck Direito Externo
--     16      |      4      |    19      |   18     |    3       ← Truck Direito Interno
--     17      |      4      |    39      |   40     |    3       ← Truck Esquerdo Externo
--     18      |      4      |    40      |   39     |    3       ← Truck Esquerdo Interno
--     19      |      4      |     7      |  NULL    |  NULL      ← Estepe
--     20      |      4      |    20      |  NULL    |  NULL      ← Estepe 2
```

- `PSCF_EIXO`: número do eixo (1, 2, 3...). NULL = posição especial (estepe).
- `PSCF_PAR`: código da posição par (o pneu do lado oposto no mesmo grupo). NULL = sem par (pneu simples ou estepe).

**Lógica de renderização do mapa:**

1. Buscar todas as posições da configuração selecionada, agrupadas por `PSCF_EIXO`.
2. Para cada eixo, renderizar uma linha horizontal representando o eixo.
3. Para cada posição do eixo, renderizar um retângulo (slot de pneu):
   - Lado **direito** (posições com "DIREITO" no nome): renderizar à direita do eixo.
   - Lado **esquerdo** (posições com "ESQUERDO" no nome): renderizar à esquerda do eixo.
   - Posições **duplas** (EXTERNO/INTERNO): renderizar dois retângulos lado a lado no mesmo lado.
4. Posições com `PSCF_EIXO = NULL` (estepes): renderizar separadamente abaixo do diagrama de eixos.
5. O diagrama deve ser uma representação SVG ou HTML/CSS — sem imagens estáticas.
6. Cada slot deve exibir: código da posição + descrição abreviada (ex: "DD" para Dianteiro Direito).
7. O mapa deve ser **reativo**: ao trocar a Configuração no select, o mapa atualiza automaticamente via AJAX/JS.

**Referência visual esperada:**

```
Veículo de frente para trás (sentido de circulação: cima → baixo)

  [DD]         [DE]          ← Eixo 1 (Dianteiro)
  
  [TDE][TDI]   [TEE][TEI]   ← Eixo 2 (Tração dupla)
  
  [LDE][LDI]   [LEE][LEI]   ← Eixo 3 (Truck duplo)
  
  [ESP1]  [ESP2]             ← Estepes (separados)
```

---

## Endpoints de API Interna (AJAX)

Criar os seguintes endpoints no padrão de rotas já existente no projeto:

```
GET /api/vehicle-models?brand_id={id}
    → Retorna modelos filtrados pela marca
    → JSON: [{ id, name, type }]

GET /api/vehicle-configurations?type={CV|CR}
    → Retorna configurações filtradas pelo tipo do veículo
    → JSON: [{ id, name, description }]

GET /api/configuration-positions?configuration_id={id}
    → Retorna as posições agrupadas por eixo para o mapa dinâmico
    → JSON: {
        axles: [
          {
            axle_number: 1,
            positions: [
              { id, code, description, side: "right|left", pair_id, is_double: true|false }
            ]
          }
        ],
        spares: [
          { id, code, description }
        ]
      }
```

---

## Arquivos a Criar

Seguindo a estrutura encontrada em `legacy/`, crie ou adapte:

### 1. Model
```
app/Models/Vehicle.php
```
- Relacionamentos: `belongsTo` para Brand, Model, Configuration, Unit, User.
- Scope para filtrar ativos: `scopeActive`.

### 2. Controller
```
app/Http/Controllers/VehicleController.php
```
- Métodos: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`.
- Métodos de API: `getModels`, `getConfigurations`, `getConfigurationPositions`.

### 3. FormRequest
```
app/Http/Requests/VehicleRequest.php
```
- Validação condicional: se `registration_method == 'plate'`, `plate` é required; se `chassis`, `chassis` é required.
- Validação de unicidade de placa/chassi.

### 4. Views (Blade)
```
resources/views/vehicles/index.blade.php
resources/views/vehicles/create.blade.php
resources/views/vehicles/edit.blade.php
resources/views/vehicles/show.blade.php
resources/views/vehicles/partials/tire-map.blade.php   ← Componente do mapa dinâmico
```

### 5. Rotas
Adicionar ao arquivo de rotas existente (verificar padrão em `legacy/` — pode ser `web.php` ou um arquivo de rotas separado por módulo):

```php
// Veículos
Route::resource('vehicles', VehicleController::class);

// API interna
Route::prefix('api')->group(function () {
    Route::get('vehicle-models', [VehicleController::class, 'getModels']);
    Route::get('vehicle-configurations', [VehicleController::class, 'getConfigurations']);
    Route::get('configuration-positions', [VehicleController::class, 'getConfigurationPositions']);
});
```

### 6. JavaScript
```
resources/js/vehicles/form.js   (ou equivalent conforme padrão legado)
```
Funcionalidades:
- Toggle placa/chassi com show/hide e atualização de required.
- AJAX para carregar modelos ao trocar marca.
- AJAX para carregar configurações ao trocar modelo.
- AJAX para renderizar o mapa de pneus ao trocar configuração.
- Função `renderTireMap(data)` que constrói o SVG/HTML do diagrama.

---

## Padrões Obrigatórios

- **Leia `legacy/` antes de criar qualquer arquivo.** Copie os padrões exatos de: estrutura de controller, uso de FormRequest, layout das views Blade, como as mensagens de erro são exibidas, como os selects são populados, como o JavaScript é incluído.
- Use os mesmos nomes de variáveis, a mesma estrutura de `@section`/`@extends` das views legadas.
- Mantenha o mesmo padrão de retorno JSON nos endpoints de API.
- Se o projeto usa Livewire, Inertia ou outra camada reativa, utilize a mesma abordagem — não misture paradigmas.
- Se houver um componente de tabela/listagem padronizado no legado, use-o na listagem de veículos.
- Respeite o sistema de autenticação/autorização já existente (middleware, gates/policies).

---

## Ordem de Execução Sugerida

1. [ ] Ler todos os arquivos em `legacy/` e mapear padrões.
2. [ ] Verificar migrations existentes — confirmar campos de `vehicles` e tabelas auxiliares.
3. [ ] Criar/ajustar `Vehicle.php` (Model).
4. [ ] Criar `VehicleRequest.php` com validações condicionais.
5. [ ] Criar `VehicleController.php` com CRUD + endpoints de API.
6. [ ] Registrar rotas.
7. [ ] Criar views Blade (index → create → edit → show).
8. [ ] Criar partial `tire-map.blade.php` com estrutura base do mapa.
9. [ ] Criar JS do formulário com toggle placa/chassi + AJAX cascata.
10. [ ] Implementar `renderTireMap()` com geração dinâmica SVG/HTML.
11. [ ] Testar fluxo completo: criação por placa, criação por chassi, mapa de pneus por configuração.

---

## Dados de Referência — Posições de Pneu

Estas são as 82 posições existentes no sistema, organizadas por tipo de slot:

| Código | Descrição                        | Tipo       |
|--------|----------------------------------|------------|
| 1      | DIANTEIRO DIREITO                | simples     |
| 2      | DIANTEIRO ESQUERDO               | simples     |
| 7      | ESTEPE                           | estepe      |
| 14     | TRAÇÃO DIREITO EXTERNO           | duplo       |
| 15     | TRAÇÃO DIREITO INTERNO           | duplo       |
| 16     | TRAÇÃO ESQUERDO EXTERNO          | duplo       |
| 17     | TRAÇÃO ESQUERDO INTERNO          | duplo       |
| 18     | TRUCK DIREITO EXTERNO            | duplo       |
| 19     | TRUCK DIREITO INTERNO            | duplo       |
| 20     | ESTEPE 2                         | estepe      |
| 21     | 1º EIXO DIREITO EXTERNO          | duplo (CR)  |
| 22     | 1º EIXO DIREITO INTERNO          | duplo (CR)  |
| 23     | 1º EIXO ESQUERDO EXTERNO         | duplo (CR)  |
| 24     | 1º EIXO ESQUERDO INTERNO         | duplo (CR)  |
| 25     | 2º EIXO DIREITO EXTERNO          | duplo (CR)  |
| 26     | 2º EIXO DIREITO INTERNO          | duplo (CR)  |
| 27     | 2º EIXO ESQUERDO EXTERNO         | duplo (CR)  |
| 28     | 2º EIXO ESQUERDO INTERNO         | duplo (CR)  |
| 29     | 3º EIXO DIREITO EXTERNO          | duplo (CR)  |
| 30     | 3º EIXO DIREITO INTERNO          | duplo (CR)  |
| 31     | 3º EIXO ESQUERDO EXTERNO         | duplo (CR)  |
| 32     | 3º EIXO ESQUERDO INTERNO         | duplo (CR)  |
| 37     | 4º EIXO DIREITO                  | simples (CR)|
| 38     | 4º EIXO ESQUERDO                 | simples (CR)|
| 39     | TRUCK ESQUERDO EXTERNO           | duplo       |
| 40     | TRUCK ESQUERDO INTERNO           | duplo       |
| ...    | (demais posições conforme tabela) |            |

**Regra de lado (direito/esquerdo):** determinar pelo nome da descrição da posição (`tire_positions.description`). Se contém "DIREITO" → lado direito do diagrama. Se contém "ESQUERDO" → lado esquerdo.

**Regra de simples/duplo:** se a posição tem um `pair_id` (PSCF_PAR not null) e a descrição contém "EXTERNO" ou "INTERNO" → é pneu duplo; caso contrário → pneu simples.

---

## Observações Finais

- O campo `VEI_ODOMETRO` do legado (`S`/`N`) controla se o veículo usa odômetro. Mantenha essa lógica.
- O campo `VEIC_TIPO` do modelo (`CV` = veículo motorizado, `CR` = carreta/reboque) determina quais configurações aparecem no select — **não misturar tipos**.
- Veículos inativos (`status = I`) devem aparecer na listagem com badge/indicador visual de inativo, seguindo o padrão das outras listagens do projeto.
- Respeite o sistema de unidades (`unit_id`) — cada veículo pertence a uma unidade.