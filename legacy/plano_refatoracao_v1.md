# Plano de Refatoracao v1

## 1) Top 8 modulos/CRUDs para iniciar (priorizado)
1. Fornecedor (`t_fornecedor`)
   - Baixa dependencia direta, alto reaproveitamento em entrada/saida/retorno, baixo risco de regressao.
2. Unidade (`t_clienteunidade`)
   - Base de segregacao multi-unidade e filtros do sistema inteiro.
3. Marca de Veiculo (`t_marcaveiculo`)
   - Cadastro base simples e dependencia direta de modelo/veiculo.
4. Modelo de Veiculo (`t_modeloveiculo`)
   - Depende de marca e desbloqueia cadastro de veiculo.
5. Marca de Pneu (`t_marcapneu`)
   - Base para tipo/configuracao de pneu.
6. Modelo de Pneu (`t_modelopneu`)
   - Depende de marca de pneu e alimenta configuracao.
7. Medida de Pneu (`t_medidapneu`)
   - Base para tipo/configuracao, baixo acoplamento estrutural.
8. Configuracao/Tipo de Pneu (`t_tipo`)
   - Consolida marca/modelo/medida e sustenta operacao com `t_pneu`.

## 2) Dependencias entre modulos
- Unidade (`t_clienteunidade`) -> Veiculo (`t_veiculo`) -> Pneu atual (`t_pneuatual`) -> Movimentacao (`t_movimentacao`)
- Fornecedor (`t_fornecedor`) -> Entrada (`t_nfcomprapneus`/`t_itensnfcomprapneus`) -> Saida (`t_saida`/`t_itenssaida`) -> Retorno (`t_retornopneu`/`t_itensretorno`)
- Marca Veiculo (`t_marcaveiculo`) -> Modelo Veiculo (`t_modeloveiculo`) -> Veiculo (`t_veiculo`)
- Marca Pneu (`t_marcapneu`) -> Modelo Pneu (`t_modelopneu`) + Medida (`t_medidapneu`) -> Tipo (`t_tipo`) -> Pneu (`t_pneu`)

## 3) Criterios de escolha do primeiro modulo
- Impacto operacional: destrava fluxos de entrada/saida/retorno sem exigir motor de movimentacao.
- Dependencia: reduzida (nao depende de outras tabelas de cadastro base para CRUD funcionar).
- Risco: baixo (validacoes simples e sem transacoes multi-tabela no CRUD base).
- Reaproveitamento: alto (referenciado por multiplos fluxos documentais).

## 4) Modulo escolhido para iniciar
- Escolhido: **Fornecedor (`t_fornecedor`)**.
- Motivos:
  - Menor dependencia para entregar CRUD completo imediatamente.
  - Alto reaproveitamento nos fluxos core (entrada, saida e retorno).
  - Serve como fundacao de cadastros base sem acoplamento alto.
  - Menor risco de regressao comparado a modulos com bloqueios por vinculo operacional.

## 5) MVP do primeiro modulo (Fornecedor)
- Entra no MVP:
  - Listagem de fornecedores.
  - Criacao e edicao com validacoes legadas (razao obrigatoria, CNPJ/telefone condicionais, email).
  - Ativacao/inativacao via `FORN_STATUS` (`A`/`I`) sem delete fisico.
  - Protecao admin (`USU_TIPO == 'A'`).
  - Conflito de duplicidade retornando 409 (criterio conservador por CNPJ quando informado).
- Fica fora do MVP:
  - Filtros avancados e paginacao server-side.
  - Auditoria detalhada de alteracoes.
  - Integracao com fluxos de entrada/saida/retorno alem da FK existente.

## 6) Checklist de QA
- Testes backend:
  - Admin cria fornecedor com sucesso.
  - Duplicidade retorna 409.
  - Nao-admin recebe 403.
  - Toggle de status altera `FORN_STATUS`.
- Rotas:
  - `GET /cadastros/fornecedor`
  - `POST /cadastros/fornecedor`
  - `PUT /cadastros/fornecedor/{id}`
  - `PATCH /cadastros/fornecedor/{id}/status`
  - Todas protegidas por `auth`, `verified` e gate admin.
- UI (Inertia/React):
  - Listagem renderiza dados legados.
  - Modal criar/editar com exibicao de erros por campo.
  - Acao de ativar/inativar com feedback visual.
- Permissoes:
  - Usuario admin (`USU_TIPO='A'`) permitido.
  - Nao-admin bloqueado com 403.

## 7) Ambiguidade documentada e decisao conservadora
- Ambiguidade:
  - O mapa especifico de Fornecedor nao explicita regra de duplicidade.
- Decisao aplicada:
  - Tratar CNPJ informado como chave de conflito operacional para evitar duplicidade de cadastro no novo fluxo.
  - Sem CNPJ informado, manter comportamento permissivo (sem conflito por CNPJ vazio).

## 8) Status de execucao
- Concluido:
  - Fornecedor (`t_fornecedor`)
  - Unidade (`t_clienteunidade`)
- Em andamento (proxima etapa):
  - Marca de Veiculo (`t_marcaveiculo`)
  - Modelo de Veiculo (`t_modeloveiculo`)
