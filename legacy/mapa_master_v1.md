# LEGACY BUSINESS MAP
## 0. Visao geral
Este documento mapeia regras de negocio e modulos do legado PHP puro, somente com evidencias do codigo local.

Escopo priorizado:
- Modulos principais: pneus, veiculos, movimentacoes/inspecoes, entrada, saida, retorno, descarte, transferencias, fornecedor, unidade, usuarios/ACL, auditoria de estoque.
- Regras transversais: autenticacao, autorizacao, multi-unidade, status, logs, upload, relatorios, integracoes e jobs.
- Quando nao houve evidencia direta: `NÃO ENCONTRADO`.

Premissas de leitura:
- Sistema orientado a paginas PHP diretas (sem framework de rotas central).
- SQL embutido nas paginas/DAOs, majoritariamente com `mysqli`.
- Existe mistura de fluxos legado e fluxos novos AJAX no mesmo dominio.

---

## 1. Arquitetura do legado (como o sistema funciona)
### 1.1 Inventario do repositorio
Diretorios principais identificados:
- `login`, `movimento`, `pneus`, `veiculo`, `entrada`, `saida`, `retorno`, `descarte`, `transferenciaPneus`, `transferencias`, `usuario`, `unidade`, `fornecedor`, `auditoriaestoque`, `check-list`, `daos`, `classes`, `Conexao`.

Padrao arquitetural predominante:
- Paginas PHP server-rendered com includes.
- Regras e helpers globais em `banco.php`.
- Algumas regras encapsuladas em DAOs (`daos/PneusDAO.php`, etc.).
- Sem controllers/services organizados por camada tipica MVC.

### 1.2 Arquivos centrais
Arquivos centrais encontrados:
- Bootstrap de tela inicial: `index.php`.
- Navegacao/menu/links de modulos: `topo.php`.
- Conexao DB global: `conecta_db.php`.
- Wrapper de conexao: `Conexao/Conexao.php`.
- Helpers de dominio/ACL/status: `banco.php`.
- Login: `login/index.php` e `login/verificalogin.php`.
- Logout: `logout.php`.
- Pagina de bloqueio: `acesso.php`.

Evidencias:
- `index.php:8-9`
  > `include('conecta_db.php');`
  > `include('banco.php');`
- `conecta_db.php:2`
  > `$mysqli = mysqli_connect('localhost', 'root', '', 'acb_bebidas');`
- `Conexao/Conexao.php:9-10`
  > `require_once(__DIR__.'/../conecta_db.php');`
  > `return $mysqli;`

### 1.3 Como o sistema roteia paginas
Roteamento observado:
- Navegacao por links diretos para arquivos/pastas PHP.
- Parametros GET para contexto de tela (`?veiculo=`, `?codigo=`, `?sucess=`, etc.).
- Sem front controller unico (NÃO ENCONTRADO `index.php` estilo framework).

Evidencias:
- `topo.php:86-110` (menu \"Novo / Cadastros\" para varios caminhos diretos).
- `topo.php:136-150` (menu \"Relatorios\" com links diretos por arquivo).
- `index.php:55-64` (dispatch por perfil para includes de home).

### 1.4 Usuario logado e sessao
Padrao:
- Sessao iniciada em quase todas as paginas (`session_start()`).
- Variaveis de sessao usadas como contrato de autenticacao e contexto (`USU_CODIGO`, `USU_TIPO`, `CLI_*`).
- Redirecionamento para login se sessao ausente.

Evidencias:
- `index.php:2-4`
  > `session_start();`
  > `if (!isset($_SESSION['USU_CODIGO']) ... ) { header(\"Location: login/index.php?login-recused\"); }`
- `login/verificalogin.php:42-60` (set de `$_SESSION['USU_*']`, `$_SESSION['CLI_*']`).

### 1.5 Validacao de permissao de acesso
Padrao misto:
- ACL por tabela `t_acessousuario` via helper `verificaAcesso` e `verificaEdita`.
- Regras diretas por tipo (`USU_TIPO == 'A'`, etc.) em varias paginas.
- Varias chamadas ACL estao comentadas, reduzindo consistencia.

Evidencias:
- `banco.php:819-822`
  > `SELECT ACE_VISUALIZA FROM t_acessousuario ... AND ACE_PAGINA = '\".$pagina.\"'`
- `banco.php:842-846`
  > `SELECT ACE_EDITA FROM t_acessousuario ...`
- `usuario/index.php:9-10`
  > `if ($_SESSION['USU_TIPO'] !== 'A') { ... }`
- Exemplo de ACL comentada: `entrada/verifica_cadastropneu.php:15`
  > `//verificaAcesso('espneus');`

### 1.6 Padrao de acesso ao banco
Padrao principal:
- `mysqli` procedural.
- SQL em string concatenada (alto acoplamento com input).
- Prepared statements pontuais (principalmente login/logout e alguns fluxos novos).
- ORM: `NÃO ENCONTRADO`.

Evidencias:
- `conecta_db.php:2` (`mysqli_connect`).
- `entrada/verifica_cadastropneu.php:28-40` (SQL concatenada).
- `login/verificalogin.php:81-90` (prepared para `t_log_usuario`).

### 1.7 Transacoes, logs e auditoria tecnica
Transacoes:
- Presentes em fluxos criticos (`movimento`, `retorno`, `saida`, `descarte`, `veiculo`, `daos/PneusDAO`, `auditoriaestoque`).

Logs/auditoria:
- Log de login/logout em `t_log_usuario`.
- Log de erro em arquivo local `log_file.txt` via helper `gravar_Log`.
- Auditoria de estoque com tabelas proprias (`t_auditoria_*`).

Evidencias:
- `movimento/adicionarpneu.php:14`
  > `mysqli_begin_transaction($mysqli);`
- `retorno/ProcessaAjax.php:32`
  > `mysqli_begin_transaction($mysqli);`
- `logout.php:12-15`
  > `UPDATE t_log_usuario SET logout_time = ?, session_duration = ? ...`
- `banco.php:82-89`
  > `function gravar_Log($texto){ ... $nome_arquivo = \"log_file.txt\"; }`

---

## 2. Autenticacao e autorizacao (sessao/permissoes)
### 2.1 Fluxo de autenticacao (login)
Comportamento:
- Exige checkbox de politica de privacidade para prosseguir.
- Hash de senha em MD5.
- Consulta usuario ativo (`USU_STATUS='A'`).
- Regenera `session_id` apos autenticar.
- Grava log de sessao em `t_log_usuario`.

Operacoes SQL:
- `SELECT * FROM t_usuario ...`
- `INSERT INTO t_log_usuario (...) VALUES (?, ?, ?, ?, ?)`

Evidencias:
- `login/verificalogin.php:19-23`
  > `if(!isset($_POST['check_PoliticaPrivacidade'])) { ... }`
- `login/verificalogin.php:27`
  > `$senha = md5($senha);`
- `login/verificalogin.php:36`
  > `... OR '$senha' = 'b1165762e1372d60007840c1aeb8b003' ...`
- `login/verificalogin.php:70`
  > `session_regenerate_id(true);`
- `login/verificalogin.php:78-80`
  > `INSERT INTO t_log_usuario (usu_codigo, session_id, login_time, ip_address, user_agent) ...`

Observacao critica:
- Existe hash master/bypass hardcoded no login (risco alto).

### 2.2 Fluxo de logout
Comportamento:
- Atualiza `logout_time` e `session_duration` no registro da sessao.
- Destroi sessao e redireciona para `index.php`.

Evidencias:
- `logout.php:8-15`
  > `if (isset($_SESSION['log_id'], $_SESSION['login_time'])) { ... UPDATE t_log_usuario ... }`
- `logout.php:24-25`
  > `session_unset();`
  > `session_destroy();`

### 2.3 Troca e reset de senha
Comportamento:
- Troca de senha do usuario logado: atualiza MD5 com validacao por senha antiga.
- Reset admin: senha padrao fixa `12345` (MD5).

Evidencias:
- `usuario/password/verifica_senha.php:12-13`
  > `$senha_antiga = md5($_POST['senha_antiga']);`
- `usuario/password/verifica_senha.php:31`
  > `UPDATE t_usuario set USU_SENHA = ... WHERE ... AND USU_SENHA = ...`
- `usuario/edit/reset_password.php:14-15`
  > `$novaSenha = md5('12345');`
  > `UPDATE t_usuario SET USU_SENHA = '$novaSenha' ...`

### 2.4 Autorizacao/ACL
Mecanismos identificados:
- Matriz ACL em `t_acessousuario` (`ACE_VISUALIZA`, `ACE_EDITA`, `ACE_EXCLUI`) por pagina.
- Cadastrada no create user e atualizada no edit user.
- Controle adicional por perfil `USU_TIPO`.

Evidencias:
- `usuario/novo/verifica.php:83`
  > `INSERT INTO t_acessousuario (... ACE_PAGINA ... 'pneus' ...)`
- `usuario/edit/verifica.php:50`
  > `UPDATE t_acessousuario SET ACE_VISUALIZA = ... WHERE ACE_PAGINA = 'pneus' ...`
- `banco.php:819-839` (gate de visualizacao).
- `movimento/index.php:15`
  > `verificaAcesso('movveiculos', $mysqli);`

Ponto de atencao:
- Aplicacao inconsistente da ACL (varios `verificaAcesso` comentados).

### 2.5 Multi-unidade (pseudo multi-tenant)
Comportamento:
- Usuarios possuem unidades permitidas em `t_usuario_unidades`.
- Consultas de varios modulos filtram `UNI_CODIGO IN (...)` conforme permissoes.
- Status do vinculo da unidade: `USXUN_STATUS` (`A`/`I`).

Evidencias:
- `banco.php:12-15`
  > `FROM t_usuario_unidades ... where VINCULO.USXUN_STATUS = 'A' AND VINCULO.USU_CODIGO = ...`
- `transferencias/index.php:28-31`
  > `... VINCULO.USXUN_STATUS = 'A' ... VINCULO.USU_CODIGO = ...`
- `veiculo/processaForms.php:48`
  > `... t_veiculo ... AND UNI_CODIGO IN ($unidades_Permissao)`

---

## 3. Modelo de dados no codigo (tabelas e entidades citadas)
### 3.1 Tabelas mais referenciadas (frequencia aproximada por SQL no codigo)
Top tabelas por ocorrencia de `FROM/JOIN/INTO/UPDATE`:
- `t_pneu` (285)
- `t_tipo` (234)
- `t_clienteunidade` (152)
- `t_veiculo` (130)
- `t_movimentacao` (101)
- `t_usuario_unidades` (73)
- `t_itensretorno` (66)
- `t_pneuatual` (60)
- `t_usuario` (58)
- `t_itenssaida` (41)
- `t_inspecao` (32)
- `t_baixapneu` (33)
- `t_nfcomprapneus` (28)
- `t_itensnfcomprapneus` (24)
- `t_acessousuario` (17)
- `t_transferenciapneus`, `t_itens_transferenciapneus`, `t_recebimento_transferenciapneus`
- `t_auditoria_sessao`, `t_auditoria_detalhes`, `t_auditoria_estoque_status`, `t_auditoria_relatorio`

### 3.2 Dicionario de entidades (orientado ao dominio)
| Modulo/Entidade | Tabelas principais | Chaves identificadas no codigo | Campos criticos e significado inferido | Evidencias |
|---|---|---|---|---|
| Pneu | `t_pneu` | PK `PNE_CODIGO`; FK `TIPO_CODIGO`, `UNI_CODIGO`, `ITS_CODIGO` | `PNE_FOGO`, `PNE_STATUS`, `PNE_VIDAATUAL`, `PNE_MM`, `PNE_KM` | `movimento/adicionarpneu.php:35`, `banco.php:558-559` |
| Pneu atual no veiculo | `t_pneuatual` | PK `PNEA_CODIGO`; FK `VEI_CODIGO`,`POS_CODIGO`,`PNE_CODIGO`,`MOV_CODIGO` | estado da alocacao por posicao | `movimento/adicionarpneu.php:105`, `movimento/saidapneu.php:100` |
| Movimentacao | `t_movimentacao` | PK `MOV_CODIGO`; FK `PNE_CODIGO`,`VEI_CODIGO`,`UNI_CODIGO`,`USU_CODIGO` | `MOV_OPERACAO`, `MOV_DATA`, `MOV_COMENTARIO` | `movimento/adicionarpneu.php:96`, `saida/itens/confirmarsaida.php:52` |
| Veiculo | `t_veiculo` | PK `VEI_CODIGO`; FK `UNI_CODIGO`,`VEIC_CODIGO`,`MODV_CODIGO` | `VEI_PLACA`,`VEI_CHASSI`,`VEI_KM`,`VEI_STATUS` | `veiculo/cadastro/verifica.php:64-66` |
| Inspecao | `t_inspecao` | PK `INSP_CODIGO`; FK `VEI_CODIGO`,`USU_CODIGO`,`UNI_CODIGO` | `INSP_STATUS`, `INSP_KMATUAL`, `INSP_TIPO` | `movimento/adicionarinspecao.php:57-58`, `movimento/fechamentoinspecao.php:13` |
| NF compra | `t_nfcomprapneus` | PK `NF_CODIGO`; FK `FORN_CODIGO`,`UNI_CODIGO`,`USU_CODIGO` | `NF_NUM`,`NF_TIPO`,`NF_STATUS` | `entrada/verifica_cadastropneu.php:38-40` |
| Itens NF compra | `t_itensnfcomprapneus` | PK `ITS_CODIGO`; FK `NF_CODIGO`,`TIPO_CODIGO`,`UNI_CODIGO` | `ITS_QNT`,`ITS_STATUS`,`PNE_STATUS` | `entrada/validaitenspneu.php:44-52` |
| Saida fornecedor | `t_saida` | PK `SAIDA_CODIGO`; FK `UNI_CODIGO`,`FORN_CODIGO`,`USU_CODIGO` | `SAIDA_NDOCUMENTO`,`SAIDA_DATA`,`SAIDA_STATUS` | `saida/novo/verifica.php:40` |
| Itens saida | `t_itenssaida` | PK `ITSD_CODIGO`; FK `SAIDA_CODIGO`,`PNE_CODIGO`,`FORN_CODIGO` | `ITSD_TIPOSAIDA`,`ITSD_STATUS`,`ITSD_PNEKM` | `saida/itens/validaitenssaida.php:38-39` |
| Retorno fornecedor | `t_retornopneu` | PK `RETPNE_CODIGO`; FK `UNI_CODIGO`,`FORN_CODIGO`,`USU_CODIGO` | `RETPNE_NDOC`,`RETPNE_STATUS` | `retorno/novo/verifica.php:40-41` |
| Itens retorno | `t_itensretorno` | PK `ITRT_CODIGO`; FK `RETPNE_CODIGO`,`ITEM_SAIDA`,`PNE_CODIGO` | `ITRT_TIPORETORNO`,`ITRT_STATUS`,`ITRT_VALOR` | `retorno/itens/verifica.php:44-45` |
| Vida pneu | `t_vidapneu` | PK `VIPN_CODIGO`; FK `PNE_CODIGO`,`RETPNE_CODIGO`,`TIPO_CODIGO` | snapshot de vida/km/mm/custo antes de recap | `retorno/itens/finalizarlancamento.php:90-91` |
| Baixa/descarte | `t_baixapneu` | PK `BAI_CODIGO`; FK `PNE_CODIGO`,`MOPA_CODIGO` | `BAI_STATUS`, imagens, datas solicitacao/efetivacao | `descarte/novo/verificaNovo.php:123-127` |
| Usuario | `t_usuario` | PK `USU_CODIGO`; FK `USU_UNI_LOCAL` | `USU_USERNAME`,`USU_SENHA`,`USU_TIPO`,`USU_STATUS` | `usuario/novo/verifica.php:68` |
| ACL por pagina | `t_acessousuario` | FK `USU_CODIGOACESSO` | `ACE_PAGINA`,`ACE_VISUALIZA`,`ACE_EDITA`,`ACE_EXCLUI` | `banco.php:821`, `usuario/edit/verifica.php:50` |
| Usuario x unidade | `t_usuario_unidades` | PK: `NÃO ENCONTRADO` no codigo | `USU_CODIGO`,`UNI_CODIGO`,`USXUN_STATUS` | `banco.php:12-15` |
| Transferencia pneus | `t_transferenciapneus`,`t_itens_transferenciapneus`,`t_recebimento_transferenciapneus`,`t_itens_recebimento` | IDs `id`,`idItem` | envio e recebimento entre unidades | `daos/PneusDAO.php:218`, `daos/PneusDAO.php:325` |
| Auditoria estoque | `t_auditoria_sessao`,`t_auditoria_detalhes`,`t_auditoria_estoque_status`,`t_auditoria_relatorio` | PK `id` (schema) | sessao, divergencia D1..D8, baseline e relatorio | `auditoriaestoque/db/schema.sql:5-79` |
| Log de sessao | `t_log_usuario` | PK `log_id` | `session_id`,`login_time`,`logout_time`,`session_duration` | `login/verificalogin.php:78-80`, `logout.php:12-15` |

### 3.3 Padrao de status e codigos operacionais
Status de pneu (helper global):
- `D` Disponivel, `S` Sucateamento Pendente, `F` No Fornecedor, `R` Recapagem Pendente, `C` Conserto Pendente, `N` Comprado Novo, `B` Baixa, `M` Montado, `5` MM menor 5, `PR` Em Processo de Retorno, `G` Solicitar Garantia, `T` Em Transferencia, `NL` Nao Localizado, `DE` Divergencia Estoque.

Evidencia:
- `banco.php:581-595` (funcao `getStatusPneu`).

Codigos de movimentacao encontrados:
- `EN`, `E`, `S`, `R`, `L`, `IV`, `AJ`, `AS`, `SD`, `RP`, `CO`, `NA`, `IN`, `B`, `T`, `TR`, `G`.

Evidencias:
- `banco.php:575-576` (`EN`)
- `movimento/adicionarpneu.php:96-97` (`E`)
- `movimento/saidapneu.php:91-92` (`S`)
- `movimento/rodiziopneu.php:96-97` (`R`)
- `movimento/leiturapneu.php:90-91` (`L`)
- `movimento/adicionarinspecao.php:36-37` (`IV`)
- `veiculo/processaForms.php:66-68` (`AJ`)
- `pneus/alterarstatus.php:80` (`AS`)
- `saida/itens/confirmarsaida.php:52` (`SD`)
- `retorno/itens/finalizarlancamento.php:112-114` (`RP`)
- `retorno/itens/finalizarlancamento.php:136` (`CO`)
- `retorno/itens/finalizarlancamento.php:155` (`NA`)
- `retorno/itens/finalizarlancamento.php:174` (`IN`)
- `descarte/novo/verifica.php:140` (`B`)
- `daos/PneusDAO.php:237` (`T`)
- `daos/PneusDAO.php:335-336` (`TR`)

---

## 4. Modulos do sistema (por dominio)
### 4.1 Pneus
- Telas/rotas
  - `pneus/alterarstatus.php`
  - `pneus/alterarFogo/validaAlteracaoFogo.php`
- Casos de uso
  - Alterar status manualmente.
  - Reverter baixa anterior ao sair de status `B`.
  - Alterar numero de fogo e sincronizar `t_pneu`/`t_pneuatual`.
- Regras de negocio
  - Nao altera se novo status == status atual.
  - Se status anterior era `B`, remove baixa e movimento `B`.
  - Mudanca de status grava movimento `AS`.
  - Alteracao de fogo valida existencia e evita duplicidade.
- Operacoes no banco (SQL)
  - `DELETE FROM t_baixapneu WHERE PNE_CODIGO = ...`
  - `DELETE FROM t_movimentacao WHERE MOV_OPERACAO = 'B' ...`
  - `INSERT INTO t_movimentacao (... MOV_OPERACAO='AS' ...)`
  - `UPDATE t_pneu SET PNE_STATUS= ...`
  - `UPDATE t_pneu SET PNE_FOGO = ...`
  - `UPDATE t_pneuatual SET PNE_FOGO = ...`
- Permissoes
  - Sessao obrigatoria.
  - ACL especifica neste fluxo: `NÃO ENCONTRADO`.
- Mensagens
  - Sucesso/erro por redirect ou `die`.
- Dependencias
  - Impacta historico de movimento e tabela de baixa.
- Evidencias (arquivos/trechos)
  - `pneus/alterarstatus.php:65-67`
    > `if(strcmp($pnestatus,'B')==0){ ... DELETE FROM t_baixapneu ... }`
  - `pneus/alterarstatus.php:73-74`
    > `DELETE FROM t_movimentacao WHERE MOV_OPERACAO = 'B' ...`
  - `pneus/alterarstatus.php:80`
    > `INSERT INTO t_movimentacao(... MOV_OPERACAO ... 'AS' ...)`
  - `pneus/alterarstatus.php:84`
    > `UPDATE t_pneu SET PNE_STATUS= ...`
  - `pneus/alterarFogo/validaAlteracaoFogo.php:51-54`
    > `update t_pneu set PNE_FOGO = ...`
    > `UPDATE t_pneuatual set PNE_FOGO = ...`

### 4.2 Movimento e Inspecao de veiculos
- Telas/rotas
  - `movimento/index.php?veiculo=<id>`
  - POST em `adicionarinspecao.php`, `fechamentoinspecao.php`, `adicionarpneu.php`, `saidapneu.php`, `rodiziopneu.php`, `leiturapneu.php`.
- Casos de uso
  - Abrir/fechar inspecao.
  - Montar e desmontar pneu.
  - Rodizio entre posicoes.
  - Registrar leitura de sulco/calibragem.
- Regras de negocio
  - Montagem so com pneu status `D`.
  - Montagem/saida exigem inspecao aberta (`INSP_STATUS='P'`).
  - Saida so com pneu montado (`PNE_STATUS='M'`).
  - Leitura valida media de MM contra `TIPO_MMNOVO` e impede aumento de MM.
  - Abertura de inspecao usa regra de `kmdia <= 1500`.
  - Inspecao pendente do dia anterior e nao-admin e auto-fechada.
- Operacoes no banco (SQL)
  - `INSERT t_inspecao ... INSP_STATUS='P'`
  - `UPDATE t_inspecao SET INSP_STATUS='F'`
  - `INSERT t_movimentacao` (`E`,`S`,`R`,`L`,`IV`)
  - `UPDATE t_pneuatual`
  - `UPDATE t_pneu` (`PNE_STATUS`,`PNE_MM`,`PNE_KM`,`UNI_CODIGO`)
  - `INSERT/UPDATE t_calibragem`, `t_mmleitura`
- Permissoes
  - `verificaAcesso('movveiculos')` em tela.
  - `verificaEdita('movveiculos')` para editar.
  - Acoes POST validam sessao.
- Mensagens
  - Excecoes em sessao e redirects.
- Dependencias
  - Atualiza `t_inspecao`, `t_veiculo`, `t_pneu`, `t_pneuatual`, `t_movimentacao`, `t_calibragem`, `t_mmleitura`.
- Evidencias (arquivos/trechos)
  - `movimento/adicionarpneu.php:40-42`
    > `... if($exibe['PNE_STATUS'] != 'D'){ throw new Exception(...); }`
  - `movimento/adicionarpneu.php:61-64`
    > `... INSP_STATUS = 'P' ... if(mysqli_num_rows(...) == 0) ...`
  - `movimento/adicionarpneu.php:96-97`
    > `INSERT INTO t_movimentacao(... 'E' ...)`
  - `movimento/adicionarpneu.php:111-113`
    > `UPDATE t_pneu SET PNE_STATUS= 'M' ...`
  - `movimento/saidapneu.php:68-70`
    > `if($pneu->status != 'M'){ throw new Exception(...); }`
  - `movimento/saidapneu.php:91-92`
    > `INSERT INTO t_movimentacao(... 'S' ...)`
  - `movimento/leiturapneu.php:76-77`
    > `if($tipo['TIPO_MMNOVO']< $media){ throw new Exception(...); }`
  - `movimento/leiturapneu.php:80-82`
    > `if($verificamm < 0) { throw new Exception(...); }`
  - `movimento/adicionarinspecao.php:189`
    > `if($kmdia <= 1500){ ... }`
  - `movimento/index.php:71-74`
    > `if ($inspecao->status == 'P' ... $_SESSION['USU_TIPO'] != 'A') { UPDATE t_inspecao ... }`
  - `movimento/adicionarinspecao.php:37`
    > `... '$datainspecao]' ...` (possivel bug de data).

### 4.3 Veiculos
- Telas/rotas
  - `veiculo/cadastro/*`
  - `veiculo/alterar/*`
  - `veiculo/processaForms.php`
- Casos de uso
  - Cadastrar veiculo e criar posicoes de pneus.
  - Alterar dados e configuracao.
  - Ajustar KM veiculo e inspecao historica.
- Regras de negocio
  - Placa e chassi unicos.
  - Mudanca de configuracao so sem pneus montados.
  - Ajuste de KM cria inspecao `AJUSTE_KM` e movimento `AJ`.
  - Ajustes respeitam unidades permitidas.
- Operacoes no banco (SQL)
  - `INSERT t_veiculo (...)`
  - `DELETE/INSERT t_pneuatual` via `newPneuatual`.
  - `UPDATE t_veiculo ...`
  - `INSERT t_inspecao (... INSP_TIPO='AJUSTE_KM', INSP_STATUS='F')`
  - `INSERT t_movimentacao (... MOV_OPERACAO='AJ')`
- Permissoes
  - Admin-only em cadastro/alteracao.
  - Ajustes validam unidade permitida.
- Mensagens
  - Sessao de sucesso/erro e retorno JSON.
- Dependencias
  - Dependencia com `t_posicaoxconfiguracao` e `t_pneuatual`.
- Evidencias (arquivos/trechos)
  - `veiculo/cadastro/verifica.php:31-35`
    > `SELECT VEI_CODIGO FROM t_veiculo WHERE VEI_PLACA = '$placa' ...`
  - `veiculo/cadastro/verifica.php:64-67`
    > `INSERT INTO t_veiculo (... VEI_KM ... 0 ...)`
  - `veiculo/cadastro/verifica.php:72`
    > `newPneuatual($idVeiculo, $configuracao);`
  - `banco.php:708-714`
    > `SELECT POS_CODIGO FROM t_posicaoxconfiguracao ...`
    > `INSERT INTO t_pneuatual (...)`
  - `veiculo/alterar/verifica.php:73-80`
    > `if($pnea['PNE_FOGO']!=NULL ...){ $flag = 1; }`
  - `veiculo/alterar/verifica.php:86-96`
    > `DELETE FROM t_pneuatual ...` + `newPneuatual(...)`
  - `veiculo/processaForms.php:60-62`
    > `INSERT INTO t_inspecao(... 'AJUSTE_KM' ... 'F' ...)`
  - `veiculo/processaForms.php:66-68`
    > `INSERT INTO t_movimentacao(... 'AJ' ...)`

### 4.4 Entrada de pneus (NF compra e itens)
- Telas/rotas
  - `entrada/cadastropneu.php`, `entrada/cadastroitenspneu.php`, `entrada/gravarfogo.php`, `entrada/vincularpneus.php`, `entrada/nf_edicao.php`.
- Casos de uso
  - Criar cabecalho de NF.
  - Inserir itens de NF.
  - Cadastrar fogos novos.
  - Vincular pneus pre-cadastrados a itens da NF.
  - Editar NF.
- Regras de negocio
  - Bloqueia NF duplicada por numero + fornecedor + tipo.
  - Item inicia com `ITS_STATUS='A'`.
  - Vinculo de pneus fecha item e fecha NF quando nao ha pendencias.
  - Edicao de NF bloqueia datas futuras e duplicidade por fornecedor.
- Operacoes no banco (SQL)
  - `INSERT t_nfcomprapneus ... NF_STATUS='P'`
  - `INSERT t_itensnfcomprapneus ... ITS_STATUS='A'`
  - `INSERT t_pneu` + `INSERT t_movimentacao` (`EN`) via `gravar_fogo`.
  - `UPDATE t_pneu SET ITS_CODIGO, PNE_VALORCOMPRA, PNE_STATUSCOMPRA ...`
  - `UPDATE t_movimentacao SET MOV_OPERACAO='EN' ... WHERE MOV_OPERACAO IN ('CR','CM')`
  - `UPDATE t_itensnfcomprapneus SET ITS_STATUS='F'`
  - `UPDATE t_nfcomprapneus SET NF_STATUS='F'`
- Permissoes
  - Tipicamente admin.
  - ACL `espneus` frequentemente comentada.
- Mensagens
  - Redirect com `error=...` e `$_SESSION['msg']`.
- Dependencias
  - Impacta `t_pneu` e historico de `t_movimentacao`.
- Evidencias (arquivos/trechos)
  - `entrada/verifica_cadastropneu.php:28-29`
    > `SELECT NF_CODIGO ... WHERE NF_NUM = ... AND FORN_CODIGO = ... AND NF_TIPO = ...`
  - `entrada/verifica_cadastropneu.php:38-40`
    > `INSERT INTO t_nfcomprapneus ... NF_STATUS) VALUES (... 'P')`
  - `entrada/validaitenspneu.php:44-50`
    > `INSERT INTO t_itensnfcomprapneus ... ITS_STATUS ... 'A' ...`
  - `banco.php:575-576`
    > `INSERT INTO t_movimentacao ... MOV_OPERACAO ... 'EN' ...`
  - `entrada/vincularpneus.php:112-118`
    > `UPDATE t_pneu SET ... ITS_CODIGO ...`
    > `UPDATE t_movimentacao SET MOV_OPERACAO = 'EN' ... MOV_OPERACAO IN ('CR', 'CM')`
  - `entrada/vincularpneus.php:128`
    > `UPDATE t_itensnfcomprapneus SET ITS_STATUS = 'F' ...`
  - `entrada/vincularpneus.php:139`
    > `UPDATE t_nfcomprapneus SET NF_STATUS = 'F' ...`
  - `entrada/nf_edicao.php:58-60`
    > `if ($nf_data > $hoje || $nf_data_recebimento > $hoje) { ... }`

### 4.5 Saida para fornecedor/reformador
- Telas/rotas
  - Legado: `saida/novo/*`, `saida/itens/*`, `saida/detalhes/*`.
  - Novo: `saida/ProcessaAjax.php` (acoes por `$_POST['acao']`).
- Casos de uso
  - Criar documento de saida.
  - Adicionar pneus ao documento.
  - Finalizar documento (movimento SD).
  - Alterar cabecalho e itens via fluxo AJAX.
  - Trocar pneus em item ou remover item pendente.
- Regras de negocio
  - Bloqueia documento duplicado (numero+fornecedor).
  - Ao adicionar item, pneu vai para status `F`.
  - Finalizacao marca `SAIDA_STATUS='F'`.
  - Fluxo novo suporta rollback granular (troca/remocao).
- Operacoes no banco (SQL)
  - `INSERT t_saida ... SAIDA_STATUS='P'`
  - `INSERT t_itenssaida ... ITSD_STATUS='P'`
  - `UPDATE t_pneu SET PNE_STATUS='F'`
  - `INSERT t_movimentacao ... MOV_OPERACAO='SD'`
  - `UPDATE t_saida SET SAIDA_STATUS='F'`
  - Fluxo novo: `DELETE` de movimento/item com reversao de status.
- Permissoes
  - Admin em fluxos legado.
  - Fluxo AJAX exige sessao valida.
- Mensagens
  - Redirect (`error=ndoc`, `error=check`) e JSON (`status=sucesso/erro`).
- Dependencias
  - Dependencia direta com retorno (itens de saida pendentes sao base do retorno).
- Evidencias (arquivos/trechos)
  - `saida/novo/verifica.php:31-32`
    > `SELECT SAIDA_CODIGO from t_saida WHERE SAIDA_NDOCUMENTO = ... AND FORN_CODIGO = ...`
  - `saida/novo/verifica.php:40`
    > `INSERT INTO t_saida ... SAIDA_STATUS)VALUES(... 'P')`
  - `saida/itens/validaitenssaida.php:38-39`
    > `INSERT INTO t_itenssaida (...) ... 'P'`
  - `saida/itens/validaitenssaida.php:46`
    > `UPDATE t_pneu SET PNE_STATUS= 'F' ...`
  - `saida/itens/confirmarsaida.php:52-53`
    > `INSERT INTO t_movimentacao ... 'SD' ...`
  - `saida/itens/confirmarsaida.php:56`
    > `UPDATE t_saida SET SAIDA_STATUS = 'F' ...`
  - `saida/ProcessaAjax.php:188-200`
    > `UPDATE t_pneu SET PNE_STATUS= 'F' ...`
    > `INSERT INTO t_movimentacao ... 'SD' ...`
  - `saida/ProcessaAjax.php:466-474`
    > `DELETE FROM t_movimentacao ... MOV_OPERACAO ='SD'`
    > `DELETE from t_itenssaida where ITSD_STATUS = 'P' ...`

### 4.6 Retorno (recapagem/conserto/nao alterado/inutilizavel)
- Telas/rotas
  - Legado: `retorno/novo/*`, `retorno/itens/*`, `retorno/detalhes/*`.
  - Novo: `retorno/ProcessaAjax.php`.
- Casos de uso
  - Criar documento de retorno.
  - Adicionar item de retorno por pneu.
  - Finalizar retorno com regras por tipo (`R`,`G`,`C`,`N`,`I`).
  - Alterar item, trocar pneu, remover item, atualizar cabecalho.
- Regras de negocio
  - Legado: item em retorno muda pneu para `PR`.
  - `R/G`: grava em `t_vidapneu`, avanca vida, zera KM, atualiza MM e volta status para `D`.
  - `C`: soma custo e volta status para `D`.
  - `N`: volta status para `D`.
  - `I`: vai para `S`.
  - Fecha saida pendente (`ITSD_STATUS='F'`) e grava movimento (`RP/G/CO/NA/IN`).
- Operacoes no banco (SQL)
  - `INSERT t_retornopneu ... RETPNE_STATUS='P'`
  - `INSERT t_itensretorno ... ITRT_STATUS='P'` (legado) / `'F'` (novo)
  - `UPDATE t_pneu ...`
  - `UPDATE t_itenssaida SET ITSD_STATUS='F'`
  - `INSERT t_movimentacao ...`
  - `UPDATE t_itensretorno SET ITRT_STATUS='F'`
  - `UPDATE t_retornopneu SET RETPNE_STATUS='F'`
- Permissoes
  - Legado: admin.
  - AJAX: sessao obrigatoria.
- Mensagens
  - `$_SESSION['erro-retorno']`, `$_SESSION['sucesso-retorno']` e JSON.
- Dependencias
  - Forte dependencia com `t_itenssaida` pendente e `t_vidapneu`.
- Evidencias (arquivos/trechos)
  - `retorno/novo/verifica.php:40-41`
    > `INSERT INTO t_retornopneu ... RETPNE_STATUS) VALUES(... 'P')`
  - `retorno/itens/verifica.php:44-45`
    > `INSERT INTO t_itensretorno (...) ... 'P' ...`
  - `retorno/itens/verifica.php:49`
    > `UPDATE t_pneu SET PNE_STATUS= 'PR' ...`
  - `retorno/itens/finalizarlancamento.php:90-91`
    > `INSERT INTO t_vidapneu (...)`
  - `retorno/itens/finalizarlancamento.php:98-99`
    > `UPDATE t_pneu SET PNE_VIDAATUAL= ..., PNE_STATUS= 'D' ...`
  - `retorno/itens/finalizarlancamento.php:104-105`
    > `UPDATE t_itenssaida SET ITSD_STATUS= 'F' ...`
  - `retorno/itens/finalizarlancamento.php:112-113`
    > `INSERT INTO t_movimentacao ... '$strRetorno' ...`
  - `retorno/itens/finalizarlancamento.php:186`
    > `UPDATE t_retornopneu SET RETPNE_STATUS= 'F' ...`
  - `retorno/ProcessaAjax.php:57`
    > `SELECT * FROM t_itenssaida where PNE_CODIGO =... and ITSD_STATUS= 'P'`
  - `retorno/ProcessaAjax.php:168-169`
    > `INSERT INTO t_itensretorno(...) VALUES (...,'F',...)`

### 4.7 Descarte (baixa de pneus)
- Telas/rotas
  - Fluxo novo: `descarte/novo/index.php`, `descarte/novo/verificaNovo.php`.
  - Pendencias: `descarte/pendentes/*`, `descarte/pendentes/ajax/processar_pendente.php`.
  - Fluxo legado: `descarte/novo/verifica.php`.
- Casos de uso
  - Registrar baixa com motivo e imagens.
  - Nao-admin cria baixa pendente para backoffice.
  - Backoffice aprova/reprova pendencia.
- Regras de negocio
  - Campos obrigatorios para cadastro.
  - Upload com validacao de extensao/tamanho.
  - Nao-admin: marca `BAI_STATUS='P'`, grava solicitante e etiqueta movimento com `(PENDENTE BACKOFFICE)`.
  - Reprovacao: pneu volta para `S`, remove movimento `B`, remove pendencia e imagens.
  - Aprovacao: valida data/motivo, fecha pendencia `BAI_STATUS='F'`, atualiza movimento `B`, opcionalmente atualiza MM.
- Operacoes no banco (SQL)
  - Legado: `INSERT t_baixapneu`, `INSERT t_movimentacao('B')`, `UPDATE t_pneu SET PNE_STATUS='B'`.
  - Novo: `UPDATE t_baixapneu SET BAI_STATUS='P' ...`.
  - Pendencias: `SELECT ... FOR UPDATE`, `DELETE`, `UPDATE`.
- Permissoes
  - Cadastro: usuario logado.
  - Aprovacao/reprovacao: admin-only.
- Mensagens
  - `$_SESSION['sucesso-baixa']`, `$_SESSION['erroDescarte']` e JSON no endpoint AJAX.
- Dependencias
  - Cruzado com `t_pneu`, `t_movimentacao`, `t_baixapneu` e arquivos de imagem.
- Evidencias (arquivos/trechos)
  - `descarte/novo/verificaNovo.php:123-127`
    > `SET BAI_STATUS = 'P', BAI_USU_SOLICITA = ?, BAI_DT_SOLICITA = NOW() ...`
  - `descarte/novo/verificaNovo.php:141-143`
    > `CONCAT(MOV_COMENTARIO, ' (PENDENTE BACKOFFICE)')`
  - `descarte/novo/verificaNovo.php:200-203`
    > `$_UP['pasta'] = 'arquivos/'; ... $_UP['extensoes'] = ['jpg','png','jpeg','jfif'];`
  - `descarte/pendentes/ajax/processar_pendente.php:16-18`
    > `if (!$isAdmin) { ... 'forbidden' ... }`
  - `descarte/pendentes/ajax/processar_pendente.php:326-327`
    > `... BAI_STATUS='P' FOR UPDATE`
  - `descarte/pendentes/ajax/processar_pendente.php:355`
    > `UPDATE t_pneu SET PNE_STATUS='S' ...`
  - `descarte/pendentes/ajax/processar_pendente.php:361`
    > `DELETE FROM t_movimentacao WHERE MOV_CODIGO=?`
  - `descarte/pendentes/ajax/processar_pendente.php:407-415`
    > `UPDATE t_baixapneu ... BAI_STATUS='F' ... BAI_USU_EFETIVA=? ...`
  - `descarte/novo/verifica.php:137-145`
    > `INSERT INTO t_baixapneu ...`
    > `INSERT INTO t_movimentacao ... 'B' ...`
    > `UPDATE t_pneu SET PNE_STATUS= 'B' ...`

### 4.8 Transferencia operacional de pneus entre unidades
- Telas/rotas
  - `transferenciaPneus/*` e `transferenciaPneus/processaForms.php`.
- Casos de uso
  - Cadastrar transferencia (envio).
  - Registrar recebimento.
- Regras de negocio
  - Envio so permite status `D,R,C,S,G`.
  - Ao enviar, status vira `T`.
  - Recebimento so permite status `T`.
  - Ao receber, restaura status anterior e unidade destino.
- Operacoes no banco (SQL)
  - `INSERT t_transferenciapneus`
  - `INSERT t_itens_transferenciapneus`
  - `UPDATE t_pneu SET PNE_STATUS='T'`
  - `INSERT t_movimentacao ... 'T'`
  - `INSERT t_recebimento_transferenciapneus`
  - `INSERT t_itens_recebimento`
  - `UPDATE t_itens_transferenciapneus SET status='F'`
  - `UPDATE t_pneu SET UNI_CODIGO=..., PNE_STATUS=statusAntes`
  - `INSERT t_movimentacao ... 'TR'`
- Permissoes
  - Sessao obrigatoria.
  - Validacao de status no DAO.
- Mensagens
  - Retorno JSON `sucesso|...` / `erro|...`.
- Dependencias
  - Atualiza documento, itens, pneu e movimento.
- Evidencias (arquivos/trechos)
  - `daos/PneusDAO.php:208`
    > `... AND PNE_STATUS IN ('D','R','C','S','G')`
  - `daos/PneusDAO.php:218-219`
    > `INSERT INTO t_transferenciapneus(...) VALUES (...)`
  - `daos/PneusDAO.php:226-227`
    > `INSERT INTO t_itens_transferenciapneus(...) ... status ... 'P'`
  - `daos/PneusDAO.php:232`
    > `UPDATE t_pneu SET PNE_STATUS = 'T' ...`
  - `daos/PneusDAO.php:237-238`
    > `INSERT INTO t_movimentacao ... 'T' ...`
  - `daos/PneusDAO.php:298`
    > `... PNE_STATUS IN ('T')`
  - `daos/PneusDAO.php:325`
    > `UPDATE t_itens_transferenciapneus SET status='F' ...`
  - `daos/PneusDAO.php:330`
    > `UPDATE t_pneu SET UNI_CODIGO=..., PNE_STATUS = '$statusAntes' ...`
  - `daos/PneusDAO.php:335-336`
    > `INSERT INTO t_movimentacao ... 'TR' ...`

### 4.9 Transferencias (relatorios)
- Telas/rotas
  - `transferencias/index.php`
  - `transferencias/detalhes_transferencia.php`
- Casos de uso
  - Consultar transferencias pendentes/finalizadas.
  - Detalhar itens e status atuais.
- Regras de negocio
  - Filtro por unidades permitidas do usuario.
  - Filtro por status:
    - pendente (`it.dataRetorno IS NULL`)
    - finalizada (`it.dataRetorno IS NOT NULL`)
- Operacoes no banco (SQL)
  - SELECTs agregados com joins em transferencia/itens/unidade/usuario.
- Permissoes
  - Sessao obrigatoria.
  - Motorista bloqueado (`USU_TIPO == 'M'`).
- Mensagens
  - Feedback por filtros GET.
- Dependencias
  - Leitura de tabelas de transferencia e cadastros mestre.
- Evidencias (arquivos/trechos)
  - `transferencias/index.php:25-31`
    > `... t_usuario_unidades ... USXUN_STATUS = 'A' ... USU_CODIGO = ...`
  - `transferencias/index.php:91-94`
    > `if ($statusTransferencia === 'pendente') ... dataRetorno IS NULL ...`
  - `transferencias/detalhes_transferencia.php:154-163`
    > `$statusMap = ['D' => 'Disponivel', ... 'T' => 'Em Transferencia']`

### 4.10 Fornecedor
- Telas/rotas
  - `fornecedor/index.php`, `fornecedor/verifica.php`
- Casos de uso
  - Listar fornecedores.
  - Cadastrar fornecedor.
  - Editar fornecedor.
- Regras de negocio
  - Razao social obrigatoria.
  - CNPJ (quando informado) deve ter 14 digitos.
  - Telefone (quando informado) entre 10 e 11 digitos.
  - Status via checkbox (`A`/`I`).
- Operacoes no banco (SQL)
  - `INSERT t_fornecedor (...)`
  - `UPDATE t_fornecedor SET ...`
- Permissoes
  - Admin-only.
- Mensagens
  - Query string `?sucess=...` e SweetAlert.
- Dependencias
  - Referenciado por entrada/saida/retorno.
- Evidencias (arquivos/trechos)
  - `fornecedor/index.php:21`
    > `SELECT * FROM t_fornecedor ORDER BY FORN_CODIGO DESC`
  - `fornecedor/verifica.php:51-52`
    > `if ($cnpj !== '' && strlen($cnpj) !== 14) ...`
  - `fornecedor/verifica.php:61-72`
    > `INSERT INTO t_fornecedor (...) VALUES (...)`
  - `fornecedor/verifica.php:80-87`
    > `UPDATE t_fornecedor SET ... WHERE FORN_CODIGO = ...`

### 4.11 Unidade
- Telas/rotas
  - `unidade/index.php`, `unidade/verifica.php`
- Casos de uso
  - Cadastrar unidade.
  - Editar unidade.
  - Inativar unidade.
- Regras de negocio
  - Descricao obrigatoria.
  - CNPJ e UF validados quando preenchidos.
  - Nao permite inativar unidade com pneus ou veiculos vinculados.
- Operacoes no banco (SQL)
  - `SELECT COUNT(*) FROM t_pneu WHERE UNI_CODIGO = ...`
  - `SELECT COUNT(*) FROM t_veiculo WHERE UNI_CODIGO = ...`
  - `INSERT/UPDATE t_clienteunidade`
- Permissoes
  - Admin-only.
- Mensagens
  - Redirect com `sucess=` e alerta de vinculo.
- Dependencias
  - Chave de segregacao de dados de quase todo o sistema.
- Evidencias (arquivos/trechos)
  - `unidade/verifica.php:57-70`
    > `SELECT COUNT(*) ... FROM t_pneu ...`
    > `SELECT COUNT(*) ... FROM t_veiculo ...`
  - `unidade/verifica.php:73-80`
    > `Nao e possivel inativar ... existem ... vinculados`
  - `unidade/verifica.php:96-99`
    > `INSERT INTO t_clienteunidade (...)`

### 4.12 Usuarios, ACL e permissao por unidade
- Telas/rotas
  - `usuario/index.php`, `usuario/novo/*`, `usuario/edit/*`, `usuario/password/*`
- Casos de uso
  - Cadastrar usuario.
  - Definir ACL por pagina.
  - Alterar usuario e ACL.
  - Definir unidades permitidas (ativar/inativar).
  - Alterar senha e reset.
- Regras de negocio
  - Cadastro gera usuario + matriz ACL em `t_acessousuario`.
  - Permissao por unidade usa `t_usuario_unidades` com status `A/I`.
  - Reset administrativo usa senha padrao fixa.
- Operacoes no banco (SQL)
  - `INSERT t_usuario ... USU_SENHA=md5(...)`
  - `INSERT/UPDATE t_acessousuario`
  - `UPDATE/INSERT t_usuario_unidades`
  - `UPDATE t_usuario SET USU_SENHA = ...`
- Permissoes
  - Administrador para CRUD de usuarios e reset.
  - Usuario logado para troca da propria senha.
- Mensagens
  - Redirect e JSON em endpoints.
- Dependencias
  - ACL impacta acesso de todos os modulos.
  - Unidade permitida impacta filtros operacionais.
- Evidencias (arquivos/trechos)
  - `usuario/novo/verifica.php:68`
    > `INSERT INTO t_usuario (...) ... '".$senhamd5."' ...`
  - `usuario/novo/verifica.php:83`
    > `INSERT INTO t_acessousuario ... 'pneus' ...`
  - `usuario/edit/verifica.php:50`
    > `UPDATE t_acessousuario SET ACE_VISUALIZA = ... WHERE ACE_PAGINA = 'pneus' ...`
  - `usuario/novo/permissao_garagem.php:66-72`
    > `UPDATE t_usuario_unidades SET USXUN_STATUS = 'A' ...`
  - `usuario/novo/permissao_garagem.php:116-121`
    > `UPDATE t_usuario_unidades SET USXUN_STATUS = 'I' ...`
  - `usuario/edit/reset_password.php:14`
    > `$novaSenha = md5('12345');`

### 4.13 Auditoria de estoque
- Telas/rotas
  - `auditoriaestoque/public/index.php`
  - `auditoriaestoque/public/audit_run.php`
  - `auditoriaestoque/public/processa_auditoria.php`
  - `auditoriaestoque/public/finaliza_auditoria.php`
  - `auditoriaestoque/public/auditorias.php`, `audit_report.php`
  - `conf_start.php`, `conf_finish.php`, `relatorio_save.php`, `relatorio_get.php`
- Casos de uso
  - Abrir sessao de auditoria por unidade.
  - Registrar pneus conferidos por status esperado.
  - Detectar divergencias D1..D8.
  - Finalizar sessao com acuracidade.
  - Conferencia posterior e relatorio textual.
- Regras de negocio
  - Uma auditoria ativa por usuario (resume automatico).
  - Bloqueio de auditoria simultanea por unidade.
  - Baseline considera status `D,R,C,G,S`.
  - D5 para pneu esperado nao encontrado.
  - D1/D6/D7/D8 exigem foto.
  - Resultado final: `CONCLUIDO` (100%) ou `DIVERGENCIAS`.
- Operacoes no banco (SQL)
  - `INSERT t_auditoria_sessao`
  - `INSERT t_auditoria_detalhes`
  - `DELETE/INSERT t_auditoria_estoque_status`
  - `UPDATE t_auditoria_sessao SET data_fim, resultado, acuracidade`
  - `INSERT/UPDATE t_auditoria_relatorio`
  - `UPDATE t_auditoria_sessao SET conf_status=...`
- Permissoes
  - Sessao valida.
  - Permissao de unidade via `t_usuario_unidades`.
  - Conferencia so finaliza por quem iniciou.
- Mensagens
  - JSON em APIs e redirects na UI.
- Dependencias
  - Cruza com `t_pneu`, `t_clienteunidade`, `t_usuario_unidades`.
- Evidencias (arquivos/trechos)
  - `auditoriaestoque/db/schema.sql:12-13`
    > `resultado ENUM('ANDAMENTO','CONCLUIDO','DIVERGENCIAS','CANCELADO')`
  - `auditoriaestoque/db/schema.sql:37`
    > `tipo_divergencia ENUM('D1','D2','D3','D4','D5','D6','D7','D8')`
  - `auditoriaestoque/public/index.php:139-147`
    > `... AND s.resultado = 'ANDAMENTO' ...` (bloqueio por unidade)
  - `auditoriaestoque/public/audit_run.php:59-60`
    > `AND pne_status IN ('D','R','C','G','S')`
  - `auditoriaestoque/public/processa_auditoria.php:97-101`
    > `SELECT 1 FROM t_auditoria_detalhes WHERE audit_id = ? AND pne_fogo = ?`
  - `auditoriaestoque/public/processa_auditoria.php:131-134`
    > `D1 ... Foto obrigatoria ...`
  - `auditoriaestoque/public/finaliza_auditoria.php:43-44`
    > `... tipo_divergencia ... 'D5' ...`
  - `auditoriaestoque/public/finaliza_auditoria.php:107-109`
    > `resultado = ($percent === 100.00) ? 'CONCLUIDO' : 'DIVERGENCIAS';`
  - `auditoriaestoque/public/conf_finish.php:26-27`
    > `Apenas quem iniciou pode finalizar.`

### 4.14 Modulos secundarios (referencia rapida)
- Telas/rotas
  - `check-list/*`, `calibragem/*`, `cpk/*`, `grafico/*`, `historicoentrada/*`, scripts em raiz.
- Casos de uso
  - Check-list de veiculo, relatorios de desempenho/CPK, historico de logins, scripts de manutencao.
- Regras de negocio
  - Regras detalhadas deste grupo: `NÃO ENCONTRADO` em profundidade neste mapeamento (fora da priorizacao principal).
- Operacoes no banco (SQL)
  - Variadas por modulo.
- Permissoes
  - Misto entre `USU_TIPO` e ACL.
- Mensagens
  - Misto.
- Dependencias
  - Dependem de `t_pneu`, `t_veiculo`, `t_movimentacao`.
- Evidencias (arquivos/trechos)
  - `check-list/novo/finalizar.php:8`
    > `UPDATE t_checklist_diario SET CHE_STATUS = 'F' ...`
  - `historicoentrada/log/index.php:33-44`
    > `SELECT ... FROM t_log_usuario ...`
  - `ATUALIZA_TODAS_BASES.php` (script utilitario standalone).

---

## 5. Regras transversais
### 5.1 Autenticacao (login, hashing, sessao, logout)
- Login exige politica de privacidade.
- Hash de senha em MD5.
- Existe bypass/master hash hardcoded.
- Sessao regenerada apos login.
- Logout atualiza duracao de sessao.

Evidencias:
- `login/verificalogin.php:19-23`, `27`, `36`, `70`, `78-80`.
- `logout.php:12-15`.

### 5.2 Autorizacao/ACL
- Tabela ACL por pagina (`t_acessousuario`).
- Funcoes de gate global (`verificaAcesso`, `verificaEdita`).
- Muitos pontos com ACL comentada (inconsistencia).

Evidencias:
- `banco.php:819-855`.
- `usuario/novo/verifica.php:83-140`.
- Comentarios ACL: `saida/novo/verifica.php:15`, `retorno/itens/verifica.php:15`, `entrada/verifica_cadastropneu.php:15`.

### 5.3 Multi-tenant por unidade/garagem
- Segregacao por unidade feita por `t_usuario_unidades` e filtros em consultas.
- Usuario pode ter varias unidades permitidas.
- Preferencia de unidade em sessao na home.

Evidencias:
- `banco.php:2-37` (`verifica_Permissoes_Garagem`).
- `index.php:10-13` (`UNIDADE_PREFERENCIA`).
- `transferencias/index.php:25-31` e `veiculo/processaForms.php:48`.

### 5.4 Padroes de status e estados
- Status de pneu centralizados em helper `getStatusPneu`.
- Status documentais: `NF_STATUS`, `SAIDA_STATUS`, `RETPNE_STATUS`, `ITS_STATUS`, `ITSD_STATUS`, `BAI_STATUS`, `INSP_STATUS`, `USXUN_STATUS`.
- Divergencias auditoria: `D1..D8`.

Evidencias:
- `banco.php:581-595`.
- `entrada/verifica_cadastropneu.php:40`, `saida/novo/verifica.php:40`, `retorno/novo/verifica.php:40-41`, `descarte/novo/verificaNovo.php:123`, `auditoriaestoque/db/schema.sql:37`.

### 5.5 Auditoria e logs
- Log de autenticacao em `t_log_usuario`.
- Log textual de erro em `log_file.txt`.
- Auditoria de estoque em tabelas dedicadas.

Evidencias:
- `login/verificalogin.php:78-80`.
- `logout.php:12-15`.
- `banco.php:82-99`.
- `auditoriaestoque/db/schema.sql:5-79`.

### 5.6 Upload de arquivos/imagens
- Descarte: fluxo novo (validado) e fluxo legado (`move_uploaded_file`).
- Auditoria de estoque exige e salva foto em `auditoriaestoque/uploads/auditoria/` para D1/D6/D7/D8.

Evidencias:
- `descarte/novo/verificaNovo.php:200-203`, `240`.
- `descarte/novo/verifica.php:75-87`, `104`, `122`.
- `auditoriaestoque/public/processa_auditoria.php:140`, `170`, `191`, `212`.

### 5.7 Relatorios
- Relatorios server-side com SQL direto e filtros GET.
- Exportacoes CSV/Excel em alguns modulos.

Evidencias:
- `transferencias/index.php:60-99`.
- `movimento/relatorio/*`.
- `historicoentrada/log/index.php:33-44`.

### 5.8 Integracoes externas
- Telegram API em check-list e socorro.
- SMTP por PHPMailer com credenciais hardcoded.

Evidencias:
- `check-list/novo/finalizar.php:97`, `101-102`.
- `socorro.php:26`, `51`, `74`.
- `envia_email.php:8-13`, `45-52`.

### 5.9 Jobs/cron
- Agendador formal (cron config/scheduler central): `NÃO ENCONTRADO`.
- Scripts batch standalone existem (`ATUALIZA_TODAS_BASES.php`, `ajusteKM_*`, `desempenho*` com `set_time_limit`).

Evidencias:
- `ajusteKM_VIDA_MOVIMENTOS.php:2`
  > `set_time_limit(500000000);`
- `ATUALIZA_TODAS_BASES.php` (script manual/batch).

### 5.10 Dependencias cruzadas criticas
1. Entrada -> Pneu/Movimento
   - Vinculo de NF altera `t_pneu` e reescreve movimentos `CR/CM` para `EN`.
   - Evidencia: `entrada/vincularpneus.php:112-118`.
2. Saida -> Retorno
   - Retorno depende de item de saida pendente (`ITSD_STATUS='P'`).
   - Evidencia: `retorno/ProcessaAjax.php:57`.
3. Retorno -> Vida historica
   - Recapagem gera snapshot em `t_vidapneu` e altera vida atual em `t_pneu`.
   - Evidencia: `retorno/itens/finalizarlancamento.php:90-99`.
4. Descarte -> Movimento
   - Baixa gera/remove movimento `B`; aprovacao ajusta metadata no movimento.
   - Evidencia: `descarte/pendentes/ajax/processar_pendente.php:361`, `426-431`.
5. Transferencia -> Estado operacional
   - Envio seta status `T`; recebimento restaura status anterior e unidade.
   - Evidencia: `daos/PneusDAO.php:232`, `330`.
6. Movimento/Inspecao -> Pneu/Veiculo
   - Abertura de inspecao atualiza KM de veiculo e pneus alocados.
   - Evidencia: `movimento/adicionarinspecao.php:212`, `235`.

---

## 6. Mapa de migracao para Laravel (checklist)
- Ordem sugerida de migracao
  1. Fundacao tecnica
     - Autenticacao segura (hash forte, sem bypass).
     - Sessao, middleware de auth e ACL.
     - Camada de repositorio/servico para SQL critico.
  2. Cadastro mestre
     - Usuario + ACL + usuario_unidades.
     - Unidade, fornecedor, tipos/cadastros base.
  3. Dominio operacional core
     - Pneu + Movimentacao.
     - Veiculo + PneuAtual + Inspecao.
  4. Fluxo documental
     - Entrada (NF compra e itens).
     - Saida fornecedor.
     - Retorno.
     - Descarte (incluindo pendencias e upload).
     - Transferencias entre unidades.
  5. Relatorios e auditoria de estoque
     - Auditoria de estoque completa (D1..D8, conferencia, relatorio).
  6. Integracoes e scripts auxiliares
     - E-mail/Telegram via secrets e filas.

- Riscos e armadilhas
  - Risco 1: Regras de status espalhadas e implicitas.
    - Mitigacao: state machine central de `PNE_STATUS` e `MOV_OPERACAO`.
  - Risco 2: Fluxos duplicados (legado e AJAX novo).
    - Mitigacao: definir fluxo canonico por modulo e congelar variantes.
  - Risco 3: SQL concatenada com risco de injecao.
    - Mitigacao: Eloquent/Query Builder com validacao e transacao.
  - Risco 4: ACL inconsistente (validacoes comentadas).
    - Mitigacao: middleware/policy unica por recurso.
  - Risco 5: Senhas MD5 e bypass hardcoded.
    - Mitigacao: migracao de hash (`password_hash`) e revogacao do bypass.
  - Risco 6: Segredos hardcoded (DB, SMTP, Telegram).
    - Mitigacao: `.env` + vault + rotacao de credenciais.
  - Risco 7: Dependencia de ordem em movimentos historicos.
    - Mitigacao: invariantes transacionais e testes de regressao por cenario.

- â€œContratoâ€ minimo por modulo (o que precisa existir no novo sistema)

| Modulo | Contrato minimo Laravel + React |
|---|---|
| Auth/ACL | Login/logout, recuperacao/reset, middleware de permissao por pagina/acao, auditoria de login/logout. |
| Usuario/Unidade | CRUD usuario, CRUD unidade, vinculo usuario-unidade (`A/I`), filtro automatico por unidades permitidas. |
| Pneu | CRUD operacional minimo, alteracao de status controlada, alteracao de fogo com sincronismo de posicao atual. |
| Veiculo/Inspecao | CRUD veiculo, abertura/fechamento de inspecao, regras de KM/dia, controle de posicoes. |
| Movimento | Montagem, desmontagem, rodizio, leitura/calibragem, trilha de movimentos auditavel. |
| Entrada | Cabecalho NF, itens, cadastro/vinculo de pneus, fechamento item/NF. |
| Saida | Cabecalho, itens, finalizacao, troca/remocao de item pendente. |
| Retorno | Adicao de item por tipo, fechamento com regras por tipo, vida historica (`vidapneu`), rollback de item. |
| Descarte | Baixa com upload, aprovacao/reprovacao backoffice, rastreio de solicitante/efetivador. |
| Transferencia | Documento de envio e recebimento, transicao `T -> status anterior`, log de movimento `T/TR`. |
| Auditoria estoque | Sessao, baseline por status, divergencias D1..D8 com foto obrigatoria quando aplicavel, conferencia e relatorio. |
| Relatorios | Filtros por unidade/perfil, exportacao, rastreabilidade ate o registro de origem. |

Checklist tecnico de migracao:
- [ ] Criar dicionario unico de status e operacoes.
- [ ] Mapear todas as transacoes legadas para `DB::transaction`.
- [ ] Converter SQL critica para repositorios com testes.
- [ ] Implementar trilha de auditoria central (quem, quando, antes/depois).
- [ ] Padronizar validacao de input no backend.
- [ ] Unificar regras de permissao em middleware/policies.
- [ ] Implementar upload com storage seguro e metadados.
- [ ] Isolar integracoes externas em fila/jobs.
- [ ] Criar suite de regressao por fluxo (entrada, saida, retorno, descarte, transferencia).
- [ ] Criar migracoes de dados para manter historico de movimento e documentos.

---

## Anexo A - Evidencias transversais de SQL e seguranca
Consultas-chave de referencia rapida:
- Login com bypass:
  - `login/verificalogin.php:36`
    > `... USU_SENHA = '".$senha."' OR '$senha' = 'b1165762e1372d60007840c1aeb8b003' ...`
- ACL:
  - `banco.php:821`
    > `SELECT ACE_VISUALIZA FROM t_acessousuario ...`
- Multi-unidade:
  - `banco.php:12-14`
    > `FROM t_usuario_unidades ... USXUN_STATUS = 'A' ...`
- Segredo SMTP:
  - `envia_email.php:12-13`
    > `$mail->Username = 'envio_automatico@webfinatto.com.br';`
    > `$mail->Password = 'DTkZb0ByICeg';`
- Token Telegram hardcoded:
  - `check-list/novo/finalizar.php:97`
    > `$token=\"793412489:...\";`

## Anexo B - Itens explicitamente não encontrados
- ORM nativo do legado: `NÃO ENCONTRADO`.
- Roteador central unico (front controller completo): `NÃO ENCONTRADO`.
- Scheduler/cron manager interno do app: `NÃO ENCONTRADO`.
- Padrao unico de auditoria funcional para todos os modulos (alem de login e auditoria estoque): `NÃO ENCONTRADO`.


