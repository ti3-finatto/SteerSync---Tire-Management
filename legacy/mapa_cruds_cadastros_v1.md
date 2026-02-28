# MAPA DE CRUDs - CADASTROS (LEGADO)
## 0) Índice rápido
- Marca de Pneu -> `t_marcapneu` -> `pneus/marca`
- Modelo de Pneu -> `t_modelopneu` -> `pneus/modelo`
- Medida de Pneu -> `t_medidapneu` -> `pneus/medida`
- Configuração/Tipo de Pneu -> `t_tipo` -> `pneus/configuracao`
- Fornecedor -> `t_fornecedor` -> `fornecedor`
- Unidade -> `t_clienteunidade` -> `unidade`
- Marca de Veículo -> `t_marcaveiculo` -> `veiculo/marca`
- Modelo de Veículo -> `t_modeloveiculo` -> `veiculo/modelo`
- Usuário + ACL -> `t_usuario` + `t_acessousuario` -> `usuario`
- Permissão de Unidade por Usuário -> `t_usuario_unidades` -> `usuario/novo` e `usuario/edit`
- Veículo (cadastro/alteração) -> `t_veiculo` (+ `t_pneuatual`) -> `veiculo/cadastro` e `veiculo/alterar`
- Segmento de Veículo -> `NÃO ENCONTRADO` (DAO ausente) -> `veiculo/cadastroSegmentoVeiculo.php`
- Configuração de Veículo/Posição (CRUD de tela) -> `NÃO ENCONTRADO` -> leitura via `funcoes.php` e script técnico `ATUALIZA_TODAS_BASES.php`

## 1) Como identificar um CRUD no legado (padrões encontrados)
- Padrões de rotas:
- `index.php` para listagem/form.
- `verifica.php` para gravação (`POST`) em vários módulos.
- Uso frequente de `?sucess=...` para feedback.
- Padrões de validação:
- `required`, `maxlength`, `min`, `max` no HTML.
- validação server-side com `mysqli_real_escape_string`, `intval`, regex e checagem de duplicidade (`SELECT COUNT`/`SELECT ... WHERE`).
- bloqueio de inativação quando há vínculo em tabelas dependentes.
- Padrões de mensagens:
- Redirect + querystring (`?sucess=enviar|update|e_*`).
- `$_SESSION[...]` para mensagens persistentes (ex.: `erro_cadastro_veiculo`, `garagens`, `sucesso_alterar_veiculo`).
- Padrões de SQL:
- `SELECT` para listagem e validação de duplicidade.
- `INSERT` e `UPDATE` como padrão dominante.
- `DELETE` físico é raro em cadastros base; mais comum é inativação por `*_STATUS`.
- Permissões:
- Predomínio de bloqueio por `$_SESSION['USU_TIPO'] == 'A'`.
- Funções ACL existentes em `banco.php` (`verificaAcesso`, `verificaEdita`), porém em vários cadastros estão comentadas.

## 2) CRUDs mapeados

### 2.1) Marca de Pneu
**Tabela:** `t_marcapneu`  
**PK:** `MARP_CODIGO`  
**Pasta(s):** `pneus/marca`

#### Rotas / telas
- GET `pneus/marca/index.php`
- GET `pneus/marca/index.php?sucess=...&msg=...`
- POST `pneus/marca/verifica.php` (`enviar` ou `update`)

#### Arquivos envolvidos
- `pneus/marca/index.php` - listagem + modal de cadastro/edição.
- `pneus/marca/verifica.php` - valida e persiste insert/update.

#### Campos e mapeamento
- `marca` -> `t_marcapneu.MARP_DESCRICAO` (string) (obrigatório) (uppercase no servidor)
- `tipo` -> `t_marcapneu.MARP_TIPO` (char: `P`/`R`) (obrigatório)
- `status` (checkbox) -> `t_marcapneu.MARP_STATUS` (`A`/`I`) (default `A`)
- `id_marca` -> usado no `WHERE MARP_CODIGO = ...` em update
- sessão `USU_CODIGO` -> `t_marcapneu.USU_CODIGO`

#### Validações
- Duplicidade por tipo + descrição (`MARP_TIPO` + `UPPER(MARP_DESCRICAO)`).
- Inativação bloqueada quando há pneus vinculados à marca (via `t_tipo` + `t_pneu`).

#### Permissões
- Acesso restrito a admin por `$_SESSION['USU_TIPO']`.

#### SQL essencial (com evidência)
- SELECT listagem: `SELECT * FROM t_marcapneu ORDER BY MARP_CODIGO DESC`
- SELECT duplicidade: `SELECT COUNT(*) AS cnt FROM t_marcapneu WHERE ...`
- INSERT: `INSERT INTO t_marcapneu (MARP_DESCRICAO, MARP_STATUS, USU_CODIGO, MARP_DATACADASTRO, MARP_TIPO) VALUES (...)`
- UPDATE: `UPDATE t_marcapneu SET ... WHERE MARP_CODIGO = ...`
- DELETE: **NÃO ENCONTRADO** (módulo usa inativação por status)

#### Fluxo e mensagens
- Redirect em `verifica.php` para `index.php?sucess=...`.
- Códigos: `enviar`, `e_enviar`, `update`, `e_update`, `e_cadastrado`, `e_vinculado`.
- Mensagens exibidas por SweetAlert em `index.php`.

#### Dependências
- `t_tipo` (marca associada ao tipo de pneu).
- `t_pneu` (verificação de vínculos ao inativar).

#### Evidências (obrigatório)
- `pneus/marca/index.php:21`
  > `SELECT * FROM t_marcapneu ORDER BY MARP_CODIGO DESC`
- `pneus/marca/index.php:106`
  > `<form id="marcaForm" method="POST" action="verifica.php">`
- `pneus/marca/verifica.php:40`
  > `SELECT COUNT(*) AS cnt FROM t_marcapneu`
- `pneus/marca/verifica.php:93`
  > `INSERT INTO t_marcapneu`
- `pneus/marca/verifica.php:103`
  > `UPDATE t_marcapneu`

### 2.2) Modelo de Pneu
**Tabela:** `t_modelopneu`  
**PK:** `MODP_CODIGO`  
**Pasta(s):** `pneus/modelo`

#### Rotas / telas
- GET `pneus/modelo/index.php`
- POST `pneus/modelo/verifica.php`

#### Arquivos envolvidos
- `pneus/modelo/index.php` - listagem e formulário/modal.
- `pneus/modelo/verifica.php` - validações e persistência.

#### Campos e mapeamento
- `marca` -> `t_modelopneu.MARP_CODIGO` (FK)
- `modelo` -> `t_modelopneu.MODP_DESCRICAO` (obrigatório, uppercase)
- `status` -> `t_modelopneu.MODP_STATUS` (`A`/`I`)
- `id_modelo` -> `WHERE MODP_CODIGO = ...`
- sessão `USU_CODIGO` -> `t_modelopneu.USU_CODIGO`

#### Validações
- Duplicidade por marca + descrição.
- Inativação bloqueada se houver pneu vinculado ao modelo (carcaça ou recapagem).

#### Permissões
- Somente admin (`USU_TIPO = 'A'`).

#### SQL essencial (com evidência)
- SELECT marcas: `SELECT MARP_CODIGO, MARP_DESCRICAO FROM t_marcapneu WHERE MARP_STATUS='A'`
- SELECT modelos: `SELECT ... FROM t_modelopneu JOIN t_marcapneu ...`
- INSERT `t_modelopneu (...) VALUES (...)`
- UPDATE `t_modelopneu SET ... WHERE MODP_CODIGO = ...`
- DELETE: **NÃO ENCONTRADO**

#### Fluxo e mensagens
- Redirect com `?sucess=`: `enviar`, `update`, `e_enviar`, `e_update`, `e_cadastrado`, `e_vinculado`.
- Alertas SweetAlert no `index.php`.

#### Dependências
- FK lógica com `t_marcapneu` (`MARP_CODIGO`).
- Verificação de vínculo em `t_tipo` + `t_pneu`.

#### Evidências (obrigatório)
- `pneus/modelo/index.php:22`
  > `SELECT MARP_CODIGO, MARP_DESCRICAO FROM t_marcapneu ...`
- `pneus/modelo/index.php:29`
  > `FROM t_modelopneu MODP JOIN t_marcapneu MARP ...`
- `pneus/modelo/verifica.php:38`
  > `SELECT COUNT(*) AS cnt FROM t_modelopneu`
- `pneus/modelo/verifica.php:94`
  > `INSERT INTO t_modelopneu`
- `pneus/modelo/verifica.php:103`
  > `UPDATE t_modelopneu`

### 2.3) Medida de Pneu
**Tabela:** `t_medidapneu`  
**PK:** `MEDP_CODIGO`  
**Pasta(s):** `pneus/medida`

#### Rotas / telas
- GET `pneus/medida/index.php`
- GET `pneus/medida/verifica.php?set=A|I&status_alter=<id>` (toggle status)
- POST `pneus/medida/verifica.php` (`enviar_medida` / `update_medida`)

#### Arquivos envolvidos
- `pneus/medida/index.php` - listagem e modal.
- `pneus/medida/verifica.php` - regras e gravação.

#### Campos e mapeamento
- `medida` -> `MEDP_DESCRICAO`
- `calibragem` -> `CAL_RECOMENDADA`
- `status` -> `MEDP_STATUS`
- `id_medida` -> `WHERE MEDP_CODIGO = ...`
- sessão `USU_CODIGO` -> `USU_CODIGO`
- `MEDP_DATACADASTRO` -> `NOW()`

#### Validações
- Duplicidade por `UPPER(MEDP_DESCRICAO)`.
- Bloqueio de inativação com pneus vinculados (`t_tipo` + `t_pneu` e `PNE_STATUS <> 'B'`).
- HTML: `calibragem` com `min=30` e `max=150`.

#### Permissões
- Somente admin (`USU_TIPO = 'A'`).

#### SQL essencial (com evidência)
- SELECT listagem: `SELECT * FROM t_medidapneu WHERE MEDP_CODIGO > 1 ...`
- SELECT vínculo: `SELECT COUNT(*) ... FROM t_tipo T JOIN t_pneu P ...`
- INSERT: `INSERT INTO t_medidapneu (...) VALUES (...)`
- UPDATE: `UPDATE t_medidapneu SET ... WHERE MEDP_CODIGO = ...`
- DELETE: **NÃO ENCONTRADO**

#### Fluxo e mensagens
- Redirects com `?sucess=`: `enviar`, `update`, `e_cadastrado`, `e_vinculado`, `e_update`.
- Mensagens via SweetAlert no front.

#### Dependências
- `t_tipo.MEDP_CODIGO`.
- `t_pneu` para regra de inativação.

#### Evidências (obrigatório)
- `pneus/medida/index.php:22`
  > `SELECT * FROM t_medidapneu WHERE MEDP_CODIGO > 1`
- `pneus/medida/verifica.php:17`
  > `if (isset($_GET['set'], $_GET['status_alter']))`
- `pneus/medida/verifica.php:74`
  > `INSERT INTO t_medidapneu`
- `pneus/medida/verifica.php:86`
  > `UPDATE t_medidapneu`

### 2.4) Configuração/Tipo de Pneu
**Tabela:** `t_tipo`  
**PK:** `TIPO_CODIGO`  
**Pasta(s):** `pneus/configuracao`

#### Rotas / telas
- GET `pneus/configuracao/index.php`
- GET `pneus/configuracao/index.php?edit=<TIPO_CODIGO>`
- POST `pneus/configuracao/verifica.php`
- GET AJAX `funcoes.php?acao=marca`
- GET AJAX `funcoes.php?acao=modelo&marca=<id>`

#### Arquivos envolvidos
- `pneus/configuracao/index.php` - form e listagem.
- `pneus/configuracao/verifica.php` - valida e grava.
- `funcoes.php` - popula selects de marca/modelo.

#### Campos e mapeamento
- `marca` -> `MARP_CODIGO`
- `modelo` -> `MODP_CODIGO`
- `medida` -> `MEDP_CODIGO`
- `desenho` -> `TIPO_DESENHO`
- `inspecao` -> `TIPO_INSPECAO`
- `nsulco` -> `TIPO_NSULCO`
- `mmnovo` -> `TIPO_MMNOVO`
- `mmseguranca` -> `TIPO_MMSEGURANCA`
- `mmpar` -> `TIPO_MMDESGPAR`
- `mmeixos` -> `TIPO_MMDESGEIXOS`
- `status` -> `TIPO_STATUS`
- `id_configuracao` -> `TIPO_CODIGO` (update)
- derivado: `TIPO_DESCRICAO` (concat de modelo + medida + desenho)

#### Validações
- Duplicidade no create por tripla `MODP_CODIGO + MARP_CODIGO + MEDP_CODIGO`.
- Regras HTML de faixa numérica (`nsulco 1..15`, mm `1..30`).
- `required` para campos principais.

#### Permissões
- Restrito a admin (`USU_TIPO = 'A'`).
- `verificaAcesso('pneus')` está comentado em `index.php`.

#### SQL essencial (com evidência)
- SELECT edição: `SELECT * FROM t_tipo WHERE TIPO_CODIGO = ...`
- INSERT: `INSERT INTO t_tipo (TIPO_STATUS, TIPO_DESCRICAO, ... ) VALUES (...)`
- UPDATE: `UPDATE t_tipo SET ... WHERE TIPO_CODIGO = ...`
- SELECT listagem: `SELECT t.*, m.MARP_DESCRICAO AS MARCA FROM t_tipo ...`
- DELETE: **NÃO ENCONTRADO**

#### Fluxo e mensagens
- Redirect final: `index.php?sucess=...`
- Códigos: `enviar`, `e_enviar`, `update`, `e_update`, `e_cadastrado`.
- Mensagens com `<div class='alert ...'>` no próprio `index.php`.

#### Dependências
- `t_marcapneu`, `t_modelopneu`, `t_medidapneu` (FKs lógicas).
- uso de `funcoes.php` para marca/modelo dinâmicos.

#### Evidências (obrigatório)
- `pneus/configuracao/index.php:107`
  > `<form ... method="POST" action="verifica.php">`
- `pneus/configuracao/index.php:143`
  > `<select id="marca" name="marca" ...>`
- `pneus/configuracao/verifica.php:61`
  > `SELECT TIPO_CODIGO FROM t_tipo WHERE MODP_CODIGO ...`
- `pneus/configuracao/verifica.php:99`
  > `insert into t_tipo (...)`
- `pneus/configuracao/verifica.php:117`
  > `update t_tipo set ... where TIPO_CODIGO = ...`
- `funcoes.php:14`
  > `if($_GET['acao'] == 'marca')`
- `funcoes.php:26`
  > `if($_GET['acao'] == 'modelo')`

### 2.5) Fornecedor
**Tabela:** `t_fornecedor`  
**PK:** `FORN_CODIGO`  
**Pasta(s):** `fornecedor`

#### Rotas / telas
- GET `fornecedor/index.php`
- POST `fornecedor/verifica.php`

#### Arquivos envolvidos
- `fornecedor/index.php` - listagem + modal.
- `fornecedor/verifica.php` - valida e grava.

#### Campos e mapeamento
- `fornecedor` -> `FORN_RAZAO` (required)
- `cnpj` -> `FORN_CNPJ` (somente dígitos)
- `email` -> `FORN_EMAIL`
- `telefone` -> `FORN_TELEFONE` (somente dígitos)
- `status` -> `FORN_STATUS` (`A`/`I`)
- sessão `USU_CODIGO` -> `USU_CODIGO`
- `id_fornecedor` -> `FORN_CODIGO` no update

#### Validações
- Razão social obrigatória.
- CNPJ, se informado, deve ter 14 dígitos.
- Telefone, se informado, entre 10 e 11 dígitos.

#### Permissões
- Somente admin (`USU_TIPO = 'A'`).

#### SQL essencial (com evidência)
- SELECT listagem: `SELECT * FROM t_fornecedor ORDER BY FORN_CODIGO DESC`
- INSERT em `t_fornecedor (...) VALUES (...)`
- UPDATE em `t_fornecedor SET ... WHERE FORN_CODIGO = ...`
- DELETE: **NÃO ENCONTRADO**

#### Fluxo e mensagens
- Redirect para `index.php?sucess=enviar|e_enviar|update|e_update`.
- SweetAlert para sucesso/erro no front.

#### Dependências
- Sem FK obrigatória de cadastro base no módulo.

#### Evidências (obrigatório)
- `fornecedor/index.php:21`
  > `SELECT * FROM t_fornecedor ORDER BY FORN_CODIGO DESC`
- `fornecedor/index.php:161`
  > `<form id="forForm" method="POST" action="verifica.php">`
- `fornecedor/verifica.php:61`
  > `INSERT INTO t_fornecedor`
- `fornecedor/verifica.php:80`
  > `UPDATE t_fornecedor SET ... WHERE FORN_CODIGO = ...`

### 2.6) Unidade
**Tabela:** `t_clienteunidade`  
**PK:** `UNI_CODIGO`  
**Pasta(s):** `unidade`, `unidade/api`

#### Rotas / telas
- GET `unidade/index.php`
- POST `unidade/verifica.php`
- GET `unidade/api/estados.php`
- GET `unidade/api/cidades.php?estado=<UF>`

#### Arquivos envolvidos
- `unidade/index.php` - listagem + modal + chamadas AJAX para UF/cidade.
- `unidade/verifica.php` - insert/update e regra de inativação.
- `unidade/api/estados.php` - retorna JSON de estados.
- `unidade/api/cidades.php` - retorna JSON de cidades por UF.

#### Campos e mapeamento
- `unidade` -> `UNI_DESCRICAO` (required)
- `status` -> `UNI_STATUS` (`A`/`I`)
- `cli_cnpj` -> `CLI_CNPJ` (somente dígitos)
- `cli_uf` -> `CLI_UF` (regex `[A-Z]{2}`)
- `cli_cidade` -> `CLI_CIDADE`
- `id_unidade` -> `UNI_CODIGO` no update

#### Validações
- Descrição obrigatória.
- CNPJ opcional, mas 14 dígitos quando informado.
- UF opcional, mas 2 letras maiúsculas quando informado.
- Inativação bloqueada se houver pneus/veículos vinculados.

#### Permissões
- Somente admin (`USU_TIPO = 'A'`).

#### SQL essencial (com evidência)
- SELECT listagem com contadores:
- `FROM t_clienteunidade u`
- subqueries em `t_pneu` e `t_veiculo` por `UNI_CODIGO`
- INSERT: `INSERT INTO t_clienteunidade (UNI_DESCRICAO, UNI_STATUS, CLI_CNPJ, CLI_UF, CLI_CIDADE) VALUES (...)`
- UPDATE: `UPDATE t_clienteunidade SET ... WHERE UNI_CODIGO = ...`
- DELETE: **NÃO ENCONTRADO**

#### Fluxo e mensagens
- Redirect `index.php?sucess=...`.
- `sucess=vinculo&msg=...` quando tentativa de inativar unidade vinculada.
- Feedback de front via SweetAlert.

#### Dependências
- `t_pneu.UNI_CODIGO`
- `t_veiculo.UNI_CODIGO`
- Dependência de dados de estados/cidades em `unidade/dados/*.json`.

#### Evidências (obrigatório)
- `unidade/index.php:26`
  > `FROM t_clienteunidade u`
- `unidade/index.php:167`
  > `<form id="unitForm" method="POST" action="verifica.php">`
- `unidade/verifica.php:58`
  > `SELECT COUNT(*) AS cnt FROM t_pneu WHERE UNI_CODIGO = {$id}`
- `unidade/verifica.php:96`
  > `INSERT INTO t_clienteunidade`
- `unidade/verifica.php:106`
  > `UPDATE t_clienteunidade SET`
- `unidade/api/cidades.php:4`
  > `$uf = $_GET['estado'] ?? '';`

### 2.7) Marca de Veículo
**Tabela:** `t_marcaveiculo`  
**PK:** `MARV_CODIGO`  
**Pasta(s):** `veiculo/marca`

#### Rotas / telas
- GET `veiculo/marca/index.php`
- GET `veiculo/marca/index.php?edit=<id>&sucess=...`
- POST `veiculo/marca/verifica.php`

#### Arquivos envolvidos
- `veiculo/marca/index.php` - form inline + listagem.
- `veiculo/marca/verifica.php` - create/update.

#### Campos e mapeamento
- `marca` -> `MARV_DESCRICAO`
- `status` -> `MARV_STATUS`
- `id_marca` -> `MARV_CODIGO` no update
- sessão `USU_CODIGO` -> `USU_CODIGO`

#### Validações
- Duplicidade por descrição exata (`SELECT MARV_CODIGO ... WHERE MARV_DESCRICAO = ...`).

#### Permissões
- Somente admin (`USU_TIPO = 'A'`).

#### SQL essencial (com evidência)
- SELECT listagem: `SELECT * FROM t_marcaveiculo ORDER BY MARV_DESCRICAO`
- INSERT em `t_marcaveiculo (...) VALUES (...)`
- UPDATE em `t_marcaveiculo SET ... WHERE MARV_CODIGO = ...`
- DELETE: **NÃO ENCONTRADO**

#### Fluxo e mensagens
- Redirect `index.php?sucess=enviar|e_enviar|e_cadastrado|update|e_update`.

#### Dependências
- Base para `t_modeloveiculo`.

#### Evidências (obrigatório)
- `veiculo/marca/index.php:117`
  > `<form ... action="verifica.php" method="POST">`
- `veiculo/marca/index.php:215`
  > `SELECT * FROM t_marcaveiculo order by MARV_DESCRICAO`
- `veiculo/marca/verifica.php:49`
  > `SELECT MARV_CODIGO FROM t_marcaveiculo WHERE MARV_DESCRICAO = ...`
- `veiculo/marca/verifica.php:54`
  > `insert into t_marcaveiculo (...)`
- `veiculo/marca/verifica.php:76`
  > `update t_marcaveiculo set ...`

### 2.8) Modelo de Veículo
**Tabela:** `t_modeloveiculo`  
**PK:** `MODV_CODIGO`  
**Pasta(s):** `veiculo/modelo`

#### Rotas / telas
- GET `veiculo/modelo/index.php`
- GET `veiculo/modelo/index.php?edit=<id>&sucess=...`
- POST `veiculo/modelo/verifica.php`
- GET AJAX `funcoes.php?acao=marcaveiculo`

#### Arquivos envolvidos
- `veiculo/modelo/index.php` - form + listagem.
- `veiculo/modelo/verifica.php` - valida/grava.
- `funcoes.php` - carga de marcas de veículo.

#### Campos e mapeamento
- `marca` -> `MARV_CODIGO`
- `modelo` -> `MODV_DESCRICAO`
- `tipoveiculo` -> `VEIC_TIPO` (`CV`/`CR`)
- `status` -> `MODV_STATUS`
- `id_modelo` -> `MODV_CODIGO` no update
- sessão `USU_CODIGO` -> `USU_CODIGO`

#### Validações
- Duplicidade por marca + descrição.

#### Permissões
- Somente admin (`USU_TIPO = 'A'`).

#### SQL essencial (com evidência)
- SELECT listagem: `SELECT ... FROM t_modeloveiculo JOIN t_marcaveiculo ...`
- INSERT: `insert into t_modeloveiculo (...) values (...)`
- UPDATE: `update t_modeloveiculo set ... where MODV_CODIGO = ...`
- DELETE: **NÃO ENCONTRADO**

#### Fluxo e mensagens
- Redirect com `?sucess=` (enviar/e_enviar/e_cadastrado/update/e_update).

#### Dependências
- FK lógica com `t_marcaveiculo`.
- Consumo por cadastro de veículo.

#### Evidências (obrigatório)
- `veiculo/modelo/index.php:121`
  > `<form ... action="verifica.php" method="POST">`
- `veiculo/modelo/index.php:271`
  > `FROM t_modeloveiculo as MODV JOIN t_marcaveiculo as MAR ...`
- `veiculo/modelo/verifica.php:50`
  > `SELECT MODV_CODIGO FROM t_modeloveiculo WHERE MARV_CODIGO ...`
- `veiculo/modelo/verifica.php:55`
  > `insert into t_modeloveiculo (...)`
- `veiculo/modelo/verifica.php:74`
  > `update t_modeloveiculo set ...`

### 2.9) Usuário + ACL (cadastro base de acesso)
**Tabela:** `t_usuario` (principal), `t_acessousuario` (ACL)  
**PK:** `USU_CODIGO` (usuário), `ACE_CODIGO` **NÃO ENCONTRADO** no código  
**Pasta(s):** `usuario`, `usuario/novo`, `usuario/edit`

#### Rotas / telas
- GET `usuario/index.php`
- GET `usuario/novo/index.php`
- GET `usuario/novo/verifica.php?...` (cadastro enviado por GET)
- GET `usuario/edit/index.php?edit=<USU_CODIGO>`
- POST `usuario/edit/verifica.php`
- GET `usuario/edit/reset_password.php?userId=<USU_CODIGO>`

#### Arquivos envolvidos
- `usuario/index.php` - listagem e links de ação.
- `usuario/novo/index.php` - formulário de criação e ACL.
- `usuario/novo/verifica.php` - INSERT em `t_usuario` + INSERTs em `t_acessousuario`.
- `usuario/edit/index.php` - edição de dados e permissões.
- `usuario/edit/verifica.php` - UPDATE em `t_usuario` + UPDATEs em `t_acessousuario`.
- `usuario/edit/reset_password.php` - reset de senha para padrão.

#### Campos e mapeamento
- `nome` -> `USU_NOME`
- `sobrenome` -> `USU_SOBRENOME`
- `username` -> `USU_USERNAME`
- `cpf` -> `USU_CPF`
- `email` -> `USU_EMAIL`
- `contato` -> `USU_TELEFONE`
- `funcao` -> `USU_TIPO`
- `unidade` -> `USU_UNI_LOCAL`
- `status` -> `USU_STATUS`
- `senha` -> `USU_SENHA` (MD5 no legado)
- checkboxes de ACL -> `t_acessousuario.ACE_VISUALIZA/ACE_EDITA/ACE_EXCLUI` por página (`pneus`, `veiculos`, `fornecedor`, `calibragem`, `relatorios`, `espneus`, `movveiculos`)

#### Validações
- Create: verificação de duplicidade de `USU_USERNAME`.
- Regras obrigatórias majoritariamente no HTML.
- Não há evidência de validação robusta de CPF/email no servidor.

#### Permissões
- Cadastros de usuário restritos a admin (`USU_TIPO = 'A'`).
- Endpoint de reset também exige `USU_TIPO = 'A'`.

#### SQL essencial (com evidência)
- SELECT listagem usuários: `SELECT ... FROM t_usuario U ORDER BY U.USU_CODIGO DESC`
- INSERT usuário: `INSERT INTO t_usuario (...) VALUES (...)`
- INSERT ACL: múltiplos `INSERT INTO t_acessousuario (...) VALUES (...)`
- UPDATE usuário: `UPDATE t_usuario SET ... WHERE USU_CODIGO = ...`
- UPDATE ACL: múltiplos `UPDATE t_acessousuario SET ... WHERE ACE_PAGINA = ...`
- UPDATE senha reset: `UPDATE t_usuario SET USU_SENHA = md5('12345') WHERE USU_CODIGO = ...`
- DELETE físico: **NÃO ENCONTRADO**

#### Fluxo e mensagens
- Create: redirect para `novo/segunda_etapa.php?sucess=...&user=<id>`.
- Duplicate username: redirect `novo/index.php?sucess=UserDuplicated`.
- Edit: redirect `usuario/index.php?editado=<id>`.
- Reset senha: retorno JSON (`success`/`message`).

#### Dependências
- `t_acessousuario` depende de `USU_CODIGO`.
- `USU_UNI_LOCAL` referencia unidade de atuação (`t_clienteunidade`).

#### Evidências (obrigatório)
- `usuario/index.php:49`
  > `SELECT ... FROM t_usuario U ORDER BY U.USU_CODIGO DESC`
- `usuario/novo/index.php:85`
  > `<form ... method="GET" action="verifica.php">`
- `usuario/novo/verifica.php:68`
  > `INSERT INTO t_usuario (...) VALUES (...)`
- `usuario/novo/verifica.php:83`
  > `INSERT INTO t_acessousuario (...) VALUES('pneus', ...)`
- `usuario/edit/verifica.php:40`
  > `UPDATE t_usuario SET ... WHERE USU_CODIGO = ...`
- `usuario/edit/verifica.php:50`
  > `UPDATE t_acessousuario SET ... WHERE ACE_PAGINA = 'pneus' ...`
- `usuario/edit/reset_password.php:15`
  > `UPDATE t_usuario SET USU_SENHA = '$novaSenha' WHERE USU_CODIGO = $userId`

### 2.10) Permissão de Unidade por Usuário
**Tabela:** `t_usuario_unidades`  
**PK:** **NÃO ENCONTRADO** no código (chave usada: `USU_CODIGO` + `UNI_CODIGO`)  
**Pasta(s):** `usuario/novo`, `usuario/edit`

#### Rotas / telas
- GET `usuario/novo/segunda_etapa.php?user=<id>`
- POST `usuario/novo/permissao_garagem.php` (single/bulk)
- POST `usuario/edit/permissao_garagem.php` (fluxo legado)

#### Arquivos envolvidos
- `usuario/novo/segunda_etapa.php` - UI de permissões por unidade.
- `usuario/novo/permissao_garagem.php` - lógica nova (bulk + single, JSON + redirect).
- `usuario/edit/permissao_garagem.php` - lógica legada (com `echo` de debug).

#### Campos e mapeamento
- `usuario_alterar` -> `USU_CODIGO`
- `garagem` / `unidades` -> `UNI_CODIGO`
- `bulk_action` -> ativa/inativa em lote
- `USXUN_STATUS` (`A`/`I`)
- `USXUN_DATACADASTRO` (timestamp)
- `USU_CADASTRO` (usuário executor)
- `USXUN_OBSERVACAO` (`PRIMEIRO_CADASTRO`, `REATIVADO`, `BULK_*`, `INATIVADO`)

#### Validações
- Sanitização de IDs numéricos (novo fluxo).
- Verificação de parâmetros obrigatórios no POST.
- Reativação por update e fallback para insert quando não existe vínculo.

#### Permissões
- `segunda_etapa.php` exige `USU_TIPO = 'A'`.
- `novo/permissao_garagem.php` exige sessão ativa (`USU_CODIGO`), sem checagem explícita de `USU_TIPO`.

#### SQL essencial (com evidência)
- SELECT vínculos/unidades em `segunda_etapa`.
- UPDATE para inativar/ativar vínculo em `t_usuario_unidades`.
- INSERT para criar vínculo de unidade.
- DELETE físico: **NÃO ENCONTRADO**

#### Fluxo e mensagens
- Bulk: resposta JSON (`ok/msg`) e reload da tela.
- Single: redirect para `segunda_etapa.php?edit=<usuario>` com `$_SESSION['garagens']`.

#### Dependências
- `t_usuario` (usuário alvo e usuário cadastro).
- `t_clienteunidade` (unidade).

#### Evidências (obrigatório)
- `usuario/novo/segunda_etapa.php:323`
  > `<form action="permissao_garagem.php" method="post">`
- `usuario/novo/permissao_garagem.php:66`
  > `UPDATE t_usuario_unidades SET USXUN_STATUS = 'A' ...`
- `usuario/novo/permissao_garagem.php:76`
  > `INSERT INTO t_usuario_unidades (...) VALUES (...)`
- `usuario/novo/permissao_garagem.php:116`
  > `UPDATE t_usuario_unidades SET USXUN_STATUS = 'I' ...`
- `usuario/edit/permissao_garagem.php:72`
  > `SELECT * FROM t_usuario_unidades WHERE USU_CODIGO = ...`

### 2.11) Veículo (cadastro e alteração)
**Tabela:** `t_veiculo` (principal), `t_pneuatual` (alocação por posição)  
**PK:** `VEI_CODIGO`  
**Pasta(s):** `veiculo/cadastro`, `veiculo/alterar`, `veiculo`

#### Rotas / telas
- GET `veiculo/cadastro/index.php`
- POST `veiculo/cadastro/verifica.php`
- GET/POST `veiculo/alterar/index.php` (consulta + form de alteração)
- POST `veiculo/alterar/verifica.php`
- GET AJAX `funcoes.php?acao=modeloveiculo&marcaveiculo=...`
- GET AJAX `funcoes.php?acao=configuracao&modeloveiculo=...`
- GET AJAX `funcoes.php?acao=placa&placa=...`

#### Arquivos envolvidos
- `veiculo/cadastro/index.php` - form de cadastro.
- `veiculo/cadastro/verifica.php` - INSERT de veículo.
- `veiculo/alterar/index.php` - busca e edição.
- `veiculo/alterar/verifica.php` - UPDATE e troca de configuração.
- `banco.php` - função `newPneuatual()` para criar posições iniciais.
- `veiculo/index.php` - listagem (atualmente com `var_dump` + `die` no topo).

#### Campos e mapeamento
- `placa` -> `VEI_PLACA`
- `chassi` -> `VEI_CHASSI`
- `frota` -> `VEI_FROTA`
- `observacao` -> `VEI_OBS`
- `unidade` -> `UNI_CODIGO`
- `modelo` -> `MODV_CODIGO`
- `configuracao` -> `VEIC_CODIGO`
- `calibragemRecomendada` / `calibragem_recomendada` -> `CAL_RECOMENDADA`
- `odometro` -> `VEI_ODOMETRO` (`S`/`N`)
- `status` -> `VEI_STATUS` (`A`/`I`)
- sistema: `VEI_KM = 0` no create, `USU_CODIGO`, `VEI_DATACADASTRO`

#### Validações
- Duplicidade de placa e chassi no create.
- Alternância de obrigatório placa/chassi por radio (`cadastro_por`).
- Faixas HTML para calibragem (`min/max`).
- Alteração de configuração bloqueada quando há pneus montados (`t_pneuatual` com `PNE_CODIGO`/`PNE_FOGO`).

#### Permissões
- Módulos de cadastro/alteração restritos a admin (`USU_TIPO = 'A'`).
- Uso de unidades permitidas (`t_usuario_unidades`) para preencher selects.

#### SQL essencial (com evidência)
- INSERT `t_veiculo (...) VALUES (...)`
- UPDATE `t_veiculo SET ... WHERE VEI_CODIGO = ...`
- DELETE `t_pneuatual` ao trocar configuração (quando permitido)
- SELECT posições da configuração: `SELECT POS_CODIGO FROM t_posicaoxconfiguracao ...`
- INSERT `t_pneuatual` para criar posições iniciais
- DELETE físico de `t_veiculo`: **NÃO ENCONTRADO**

#### Fluxo e mensagens
- Create:
- `$_SESSION['erro_cadastro_veiculo']` para duplicidade/erro.
- `$_SESSION['sucess']` em sucesso.
- redirect para `index.php` com `?sucess=enviar`.
- Update:
- `?sucess=e_pneus|e_cadastrado|enviar`.
- `$_SESSION['erro_alterar_veiculo']` / `$_SESSION['sucesso_alterar_veiculo']`.

#### Dependências
- `t_modeloveiculo`, `t_marcaveiculo`, `t_clienteunidade`, `t_veiculoconfiguracao`.
- `t_posicaoxconfiguracao` + `t_pneuatual` via `newPneuatual`.

#### Evidências (obrigatório)
- `veiculo/cadastro/index.php:103`
  > `<form ... method="POST" action="verifica.php">`
- `veiculo/cadastro/verifica.php:64`
  > `INSERT INTO t_veiculo (...) VALUES (...)`
- `veiculo/cadastro/verifica.php:72`
  > `newPneuatual($idVeiculo, $configuracao);`
- `banco.php:708`
  > `SELECT POS_CODIGO FROM t_posicaoxconfiguracao where VEIC_CODIGO = ...`
- `banco.php:713`
  > `INSERT INTO t_pneuatual (MOV_CODIGO, VEI_CODIGO, POS_CODIGO) VALUES (...)`
- `veiculo/alterar/verifica.php:46`
  > `UPDATE t_veiculo SET VEI_STATUS = ..., VEI_ODOMETRO = ...`
- `veiculo/alterar/verifica.php:86`
  > `DELETE FROM t_pneuatual WHERE VEI_CODIGO = ...`
- `veiculo/index.php:3`
  > `var_dump($_SESSION);`
- `veiculo/index.php:7`
  > `die();`

### 2.12) Segmento de Veículo (cadastro incompleto no repositório)
**Tabela:** `NÃO ENCONTRADO`  
**PK:** `NÃO ENCONTRADO`  
**Pasta(s):** `veiculo/cadastroSegmentoVeiculo.php`, `veiculo/processaForms.php`, `veiculo/processaAjax.php`

#### Rotas / telas
- GET `veiculo/cadastroSegmentoVeiculo.php`
- POST AJAX `veiculo/processaForms.php` (`tipoForm=form_cadastrarEditarSegmentoVeiculo`)
- POST AJAX `veiculo/processaAjax.php` (`requisicao=buscarSegmentosVeiculo`)

#### Arquivos envolvidos
- `veiculo/cadastroSegmentoVeiculo.php` - tela e modal.
- `veiculo/processaForms.php` - recebe form e delega para DAO.
- `veiculo/processaAjax.php` - busca listagem via DAO.
- `daos/SegmentoVeiculoDAO.php` - **NÃO ENCONTRADO no repositório**.

#### Campos e mapeamento
- `idSegmento` -> **NÃO ENCONTRADO**
- `descricaoSegmento` -> **NÃO ENCONTRADO**
- `status` -> **NÃO ENCONTRADO**

#### Validações
- Verificação de obrigatórios no `processaForms.php`.

#### Permissões
- Tela exige `USU_TIPO = 'A'`.

#### SQL essencial (com evidência)
- SELECT/INSERT/UPDATE/DELETE: **NÃO ENCONTRADO** (dependia de DAO ausente).

#### Fluxo e mensagens
- Resposta via JSON `status|mensagem` no `processaForms.php`.

#### Dependências
- Dependência direta de `SegmentoVeiculoDAO` ausente.

#### Evidências (obrigatório)
- `veiculo/cadastroSegmentoVeiculo.php:11`
  > `include("../daos/SegmentoVeiculoDAO.php");`
- `veiculo/processaForms.php:168`
  > `if (strcmp($tipoForm, 'form_cadastrarEditarSegmentoVeiculo') == 0)`
- `veiculo/processaForms.php:182`
  > `$retSegmento = $segmentoDAO->cadastrarEditarSegmentoVeiculo($segmento);`
- `veiculo/processaAjax.php:113`
  > `if(strcmp($requisicao, 'buscarSegmentosVeiculo') == 0)`

### 2.13) Configuração de Veículo / Posição (CRUD de tela)
**Tabela:** `t_veiculoconfiguracao`, `t_posicaoxconfiguracao`, `t_posicao`  
**PK:** `NÃO ENCONTRADO` (CRUD de tela)  
**Pasta(s):** `funcoes.php`, `veiculo/cadastro`, `ATUALIZA_TODAS_BASES.php`

#### Rotas / telas
- GET AJAX `funcoes.php?acao=configuracao&modeloveiculo=<id>`
- Não foi encontrada tela dedicada de CRUD (`index/verifica`) para essas tabelas.

#### Arquivos envolvidos
- `funcoes.php` - leitura de configurações por tipo de veículo.
- `veiculo/cadastro/index.php` - consume configuração no select.
- `ATUALIZA_TODAS_BASES.php` - script técnico de carga de `t_posicaoxconfiguracao`.

#### Campos e mapeamento
- `NÃO ENCONTRADO` para form CRUD dedicado.

#### Validações
- `NÃO ENCONTRADO` para CRUD dedicado.

#### Permissões
- Herdadas das telas consumidoras (ex.: cadastro/alteração de veículo admin).

#### SQL essencial (com evidência)
- SELECT configuração:
- `SELECT VEIC_CODIGO, VEIC_DESCRICAO FROM t_veiculoconfiguracao WHERE ...`
- Script técnico:
- `DELETE FROM t_posicaoxconfiguracao WHERE 1`
- `INSERT INTO t_posicaoxconfiguracao (...) VALUES (...)`
- CRUD de tela (INSERT/UPDATE/DELETE por formulário): **NÃO ENCONTRADO**

#### Fluxo e mensagens
- `NÃO ENCONTRADO` para módulo CRUD dedicado.

#### Dependências
- Consumido por cadastro/alteração de veículo para definição de eixos/posições.

#### Evidências (obrigatório)
- `funcoes.php:133`
  > `SELECT VEIC_CODIGO, VEIC_DESCRICAO FROM t_veiculoconfiguracao ...`
- `veiculo/cadastro/index.php:195`
  > `<select id="configuracao" name="configuracao" ...>`
- `ATUALIZA_TODAS_BASES.php:162`
  > `DELETE FROM t_posicaoxconfiguracao WHERE 1`
- `ATUALIZA_TODAS_BASES.php:164`
  > `INSERT INTO t_posicaoxconfiguracao (...) VALUES (...)`

## 3) Ordem sugerida de migração (cadastros)
1. Marca de Veículo (`t_marcaveiculo`)
2. Modelo de Veículo (`t_modeloveiculo`)
3. Unidade (`t_clienteunidade`)
4. Fornecedor (`t_fornecedor`)
5. Marca de Pneu (`t_marcapneu`)
6. Modelo de Pneu (`t_modelopneu`)
7. Medida de Pneu (`t_medidapneu`)
8. Configuração/Tipo de Pneu (`t_tipo`)
9. Usuário base (`t_usuario`)
10. ACL por página (`t_acessousuario`)
11. Permissão de Unidade por Usuário (`t_usuario_unidades`)
12. Veículo cadastro (`t_veiculo`) + criação de posições (`t_pneuatual`)
13. Veículo alteração (troca de configuração com regra de pneus montados)
14. Segmento de Veículo (somente após recuperar DAO/tabela ausente)
15. Configuração de veículo/posição (somente após definir estratégia: CRUD de tela x seed/script)
