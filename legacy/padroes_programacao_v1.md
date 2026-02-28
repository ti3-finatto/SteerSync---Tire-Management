# PADRÕES DE PROGRAMAÇÃO — Refatoração Legado -> Laravel + React (Inertia)
**Versão:** v2  
**Objetivo:** Este documento define padrões obrigatórios de implementação para que qualquer alteração feita pelo Codex siga um estilo único, seguro e previsível, preservando características do banco legado e facilitando a migração de dados.

---

## 0) Princípios inegociáveis
1. **Não inventar colunas/tabelas**: qualquer campo usado deve existir no `legacy/schema.sql` (ou ser explicitamente autorizado em uma task).
2. **Não mudar regras do legado por "melhoria"**: se for melhorar, deve ser em uma task separada e documentada.
3. **Sem "mágica"**: toda regra de negócio deve apontar para:
   - evidência no `legacy/mapa_master_v1.md` / `legacy/mapa_cruds_cadastros_v1.md`, ou
   - SQL existente no `legacy/schema.sql`.
4. **Compatibilidade de dados primeiro**: o objetivo é migrar dados com o mínimo de fricção.
5. **Erros e validações sempre explícitos**: nunca "silenciar" erro. Sempre retornar status HTTP adequado e mensagem consistente.

---

## 1) Banco de dados (MySQL legado)
### 1.1 Charset/Collation padrão
- Usar sempre:
  - `utf8mb4`
  - `utf8mb4_unicode_ci`

### 1.2 Tabelas legadas e chaves
- Tabelas legadas começam com `t_` (ex.: `t_usuario`, `t_fornecedor`).
- Chave primária geralmente é `*_CODIGO` (ex.: `USU_CODIGO`, `FORN_CODIGO`, `MARP_CODIGO`).
- **Autenticação** usa `t_usuario` e **chave** `USU_CODIGO`.  
  **Não usar `users`** e não depender do `App\Models\User` padrão.

### 1.3 Timestamps
- Não assumir `created_at/updated_at` nas tabelas legadas.
- Em Eloquent:
  - Se a tabela não tiver timestamps padrão, configurar: `public $timestamps = false;`
- Só usar `created_at/updated_at` se existir no schema OU se a task pedir explicitamente para adicionar.

### 1.4 Status e inativação
- Preferir **inativação** via `*_STATUS` (`A/I`, `P/F`, etc.), como no legado.
- Evitar `DELETE` físico, exceto se:
  - o legado realmente deleta, ou
  - a task pedir explicitamente.

### 1.5 Consultas e performance
- Para verificações de vínculo, usar sempre:
  - `->exists()` / `->count()` com índices (quando houver).
- Evitar `SELECT *` em rotas de listagem. Selecionar só colunas usadas.

---

## 2) Arquitetura Laravel (Backend)
### 2.1 Padrão de camadas
Para cada módulo/CRUD:
- **Model** (Eloquent) -> `app/Models/Legacy/...` (preferível para separar do novo)
- **Requests** (validação) -> `app/Http/Requests/...`
- **Controller** -> `app/Http/Controllers/...`
- **Service** (regras de negócio/transações) -> `app/Services/...` (quando houver regra além de CRUD simples)
- **Policies/Gates** -> `app/Policies/...` ou `AuthServiceProvider`
- **Routes** -> `routes/web.php` (Inertia) e/ou `routes/api.php` (se for API)

> Regra: CRUD simples pode ficar só em Controller + Request.  
> Se houver transação, vínculo, regras de status, ou múltiplas tabelas => criar `Service`.

### 2.2 Nomenclatura
- Model: `MarcaPneu`, `ModeloPneu`, `Fornecedor`, `Unidade`, etc.
- Tabela: manter o nome legado com `protected $table = 't_marcapneu';`
- PK: `protected $primaryKey = 'MARP_CODIGO';`
- PK não auto increment? Se for o caso no schema, declarar `public $incrementing = false;` e `$keyType`.

### 2.3 Mass assignment
- Usar `$fillable` estritamente com campos permitidos.
- Proibir `$guarded = [];` em modelos legados.

### 2.4 Validação (FormRequest obrigatório)
- Toda rota `store/update` deve usar um FormRequest.
- Validar:
  - required
  - max length
  - formatos (CPF/CNPJ/telefone)
  - duplicidade (unique condicional conforme legado)
  - faixas numéricas (ex.: calibragem min/max)
- Normalização no Request:
  - `trim()`
  - uppercase quando legado usa uppercase
  - `onlyDigits` para cpf/cnpj/telefone

### 2.5 Regras de duplicidade (padrão)
Quando o legado valida duplicidade por combinação (ex.: tipo + descrição):
- Implementar com query builder:
  - `Model::where(...)->whereRaw('UPPER(col) = ?', [mb_strtoupper($v)])`
- Em update, ignorar o próprio registro.

### 2.6 Regra de vínculo (bloqueio de inativação)
- Implementar com `exists()` e retornar **HTTP 409 (Conflict)** com mensagem clara:
  - `"Não é possível inativar: existe vínculo com ..."`

### 2.7 Transações
- Quando envolver 2+ alterações críticas (ou histórico/movimentação):
  - usar `DB::transaction(function () { ... });`
- Nunca iniciar transação sem tratar exceção.

### 2.8 Erros e respostas
Para rotas Inertia:
- Em validação: 422 automático (FormRequest)
- Em autorização: 403
- Em vínculo/duplicidade: 409
- Em sucesso:
  - redirecionar com `->with('success', '...')` ou `->withErrors(...)` quando necessário
- Não usar `dd()`, `dump()`, `die()`.

### 2.9 Logging
- Registrar logs apenas quando necessário:
  - operações críticas (ex.: alteração de status, auditoria, etc.)
- Usar `Log::info/warning/error` com contexto (usuário, tabela, id).

---

## 3) Autenticação (t_usuario / USU_CODIGO)
### 3.1 Contrato
- O usuário autenticado deve ser identificado por `USU_CODIGO`.
- Toda referência a usuário em tabelas deve preservar `USU_CODIGO` (ou colunas compatíveis existentes).

### 3.2 Autorização
- Regra base:
  - admin no legado normalmente é `USU_TIPO == 'A'`
- Implementar `Gate::define('admin', fn($u) => $u->USU_TIPO === 'A');`
- Em módulos que tinham ACL por página, documentar e implementar em etapa própria (não inventar sem task).

---

## 4) Frontend (React 19 + TS + Inertia)
### 4.1 Estrutura
- Páginas: `resources/js/pages/...`
- Componentes reutilizáveis: `resources/js/components/...`
- Tipos: `resources/js/types/...`

### 4.2 Padrão de página CRUD (Inertia)
Cada CRUD deve ter:
- `Index.tsx` com:
  - listagem paginada (quando necessário)
  - modal ou página separada para criar/editar
  - filtro simples quando legado tinha (ex.: status)
- Form:
  - `useForm` do Inertia
  - erros exibidos por campo
  - botão com loading state
- Não duplicar lógica de validação no frontend: apenas UX (ex.: máscara), validação final é do backend.

### 4.3 Tipagem
- Todo payload vindo do backend deve ter tipo TS correspondente em `resources/js/types`.
- Evitar `any`. Se faltar campo, criar type parcial.

### 4.4 Mensagens
- Usar flash messages do Inertia:
  - `success`, `error`
- Nunca usar alert() puro. Preferir toast/component padrão do projeto (shadcn/ui, se já existir).

---

## 5) Rotas e padrões REST
### 5.1 Padrão recomendado
Para cadastros:
- `GET /cadastros/<recurso>` (index)
- `POST /cadastros/<recurso>` (store)
- `PUT/PATCH /cadastros/<recurso>/{id}` (update)
- `PATCH /cadastros/<recurso>/{id}/status` (toggle ativar/inativar)

### 5.2 Nomes
- Rotas nomeadas: `cadastros.<recurso>.*`
- Ex.: `cadastros.marca-pneu.index`, `.store`, `.update`, `.toggleStatus`

---

## 6) Migrações e seed
### 6.1 Regra
- Não criar migrações "inventadas".
- Se for preciso ajustar schema, deve:
  1) ser explicitamente pedido na task
  2) preservar collation/charset
  3) evitar quebra de migração de dados

### 6.2 Seeds
- Seeds só quando legado usava carga técnica/script (ex.: configurações fixas).
- Caso contrário, dados virão da migração do legado.

---

## 7) Testes (mínimo obrigatório por CRUD)
Para cada CRUD novo:
1. **Feature Test**:
   - admin consegue criar
   - duplicidade retorna 409 (quando aplicável)
   - inativação com vínculo retorna 409
2. **Auth Test**:
   - usuário não-admin recebe 403 (quando aplicável)

> Em ambiente SQLite de testes: se houver dependência de MySQL, documentar e adaptar com conditional (sem quebrar suite).

---

## 8) UI — Sidebar (Shadcn/ui + Tailwind)

Esta seção documenta padrões obrigatórios para a sidebar do sistema, baseados em problemas já corrigidos. Qualquer alteração na sidebar deve respeitar estas regras.

### 8.1 Alinhamento de ícones quando colapsada

Quando `state === 'collapsed'`, **todos** os elementos interativos da sidebar (itens de menu, logo, avatar do usuário) devem ser explicitamente centralizados. O Shadcn Sidebar com `collapsible="icon"` **não centraliza automaticamente** — é necessário aplicar as classes nos três níveis:

```tsx
// ✅ CORRETO — aplicar nos três níveis
<SidebarMenu className={cn(isCollapsed && 'items-center')}>
  <SidebarMenuItem className={cn(isCollawed && 'flex w-full justify-center')}>
    <SidebarMenuButton className={cn(isCollapsed && 'justify-center gap-0 px-0 [&>svg]:mx-auto')}>
```

```tsx
// ❌ ERRADO — aplicar só no botão não é suficiente
<SidebarMenuButton className={cn(isCollapsed && 'justify-center')}>
```

Isso se aplica a: logo no `SidebarHeader`, itens de menu no `NavMain`, e avatar do usuário no `NavUser`.

### 8.2 Padding lateral dos containers quando colapsada

O `SidebarHeader`, `SidebarFooter` e `SidebarGroup` têm `px-*` que deslocam o conteúdo para fora do centro quando a sidebar está colapsada. Remover o padding lateral nesses containers ao colapsar:

```tsx
// ✅ CORRETO
<SidebarHeader className={cn('py-2', isCollapsed ? 'px-0' : 'px-2')}>
<SidebarGroup className={cn('py-0.5', !isCollapsed && 'px-1')}>
```

```tsx
// ❌ ERRADO — px fixo desalinha os ícones
<SidebarHeader className="px-2 py-2">
<SidebarGroup className="px-1 py-0.5">
```

### 8.3 Estado ativo dos itens de menu

Usar `outline` em vez de `ring` para o contorno do item ativo. O `ring` é clipado pelo `overflow-hidden` do botão e aparece cortado nas laterais. O `outline` não participa do box model e nunca é clipado:

```tsx
// ✅ CORRETO
'data-[active=true]:outline data-[active=true]:outline-1 data-[active=true]:outline-primary/40'

// ❌ ERRADO — ring é clipado pelo overflow
'data-[active=true]:ring-1 data-[active=true]:ring-primary/25'
```

Também trocar `overflow-hidden` por `overflow-visible` no `itemButtonClass`:

```tsx
// ✅ CORRETO
'overflow-visible whitespace-nowrap'

// ❌ ERRADO
'overflow-hidden whitespace-nowrap'
```

### 8.4 Logo colapsada — usar imagem em vez de SVG padrão

Ao usar `collapsible="icon"`, substituir o componente `AppLogoIcon` (que usa o SVG padrão do Laravel) por um `<img>` apontando para os arquivos de marca em `public/brand/`. Suportar tema claro/escuro:

```tsx
// ✅ CORRETO
{isCollapsed ? (
  <>
    <img src="/brand/icone-dark.ico" alt="Logo" className="size-6 dark:hidden" />
    <img src="/brand/icone-light.ico" alt="Logo" className="hidden size-6 dark:block" />
  </>
) : (
  <AppLogo />
)}
```

Remover o import de `AppLogoIcon` após substituir.

### 8.5 Seções colapsáveis no NavMain

Quando a sidebar está colapsada, as seções devem permanecer abertas (ou ser tratadas como abertas) para que os ícones apareçam. Usar `CollapsibleContent` apenas para subitens, nunca para o grupo inteiro quando colapsado:

```tsx
// ✅ CORRETO — subitens só aparecem quando expanded
{!isCollapsed && (
  <CollapsibleContent>
    <SidebarMenuSub>...</SidebarMenuSub>
  </CollapsibleContent>
)}
```

---

## 9) UI — Header da aplicação (AppSidebarHeader)

### 9.1 Layout responsivo obrigatório

O header deve ter **dois layouts distintos** — mobile e desktop — usando `md:hidden` / `md:flex`. Nunca usar um único layout que tente se adaptar via responsividade parcial, pois resulta em elementos espremidos no mobile.

```tsx
{/* Mobile: trigger | logo centralizada | avatar */}
<div className="flex w-full items-center justify-between md:hidden">
  <SidebarTrigger />
  <BrandLogo />
  <Avatar ... />
</div>

{/* Desktop: trigger + breadcrumbs + busca + tema + avatar */}
<div className="hidden w-full items-center gap-3 md:flex">
  ...
</div>
```

### 9.2 Mobile — elementos permitidos no header

No mobile, manter apenas o essencial: trigger da sidebar, logo e avatar. Remover do mobile: breadcrumbs, barra de busca, botão de alternância de tema. Esses elementos poluem o header em telas pequenas.

---

## 10) UI — Componentes de tabela (DataCard + DataTable)

### 10.1 Evitar containers aninhados com bordas duplas

O componente `DataCard` (baseado em `Card` do shadcn) já fornece borda e `border-radius`. O `DataTable` **não deve** adicionar sua própria borda ou `border-radius`, pois cria o efeito visual de "caixa dentro de caixa":

```tsx
// ✅ CORRETO — DataTable sem borda própria
export default function DataTable({ children, className }) {
  return (
    <div className={cn('overflow-x-auto', className)}>
      {children}
    </div>
  );
}

// ❌ ERRADO — borda duplicada com o Card pai
export default function DataTable({ children, className }) {
  return (
    <div className={cn('overflow-hidden rounded-lg border', className)}>
      {children}
    </div>
  );
}
```

### 10.2 overflow-hidden no DataCard

O `DataCard` deve ter `overflow-hidden` para que o `border-radius` do Card recorte corretamente as bordas da tabela nos cantos:

```tsx
// ✅ CORRETO
<Card className={cn('overflow-hidden shadow-sm', className)}>

// ❌ ERRADO — tabela "vaza" visualmente nos cantos arredondados
<Card className={cn('shadow-sm', className)}>
```

### 10.3 Scroll horizontal em tabelas

Tabelas com muitas colunas devem ter scroll horizontal em telas pequenas. O `DataTable` deve sempre ter `overflow-x-auto`:

```tsx
<div className={cn('overflow-x-auto', className)}>
  {children}
</div>
```

---

## 11) Checklist do Codex antes de finalizar uma task
Antes de concluir qualquer implementação, o Codex deve:
- [ ] Confirmar tabela/colunas no `legacy/schema.sql`
- [ ] Confirmar regra no `legacy/mapa_master_v1.md` ou `legacy/mapa_cruds_cadastros_v1.md`
- [ ] Criar/ajustar Model com `$table`, `$primaryKey`, `$timestamps`
- [ ] Criar FormRequest com validação e normalização
- [ ] Implementar Controller/Service com:
  - autorização
  - validações de duplicidade/vínculo
  - transação quando necessário
- [ ] Implementar página React com formulário e mensagens
- [ ] Criar/atualizar types TS
- [ ] Adicionar testes mínimos
- [ ] Garantir que não existe `dd/die/var_dump` e que os imports estão limpos
- [ ] **[UI]** Sidebar colapsada: verificar alinhamento de ícones, logo e avatar (seção 8)
- [ ] **[UI]** Header: verificar layout mobile separado do desktop (seção 9)
- [ ] **[UI]** Tabelas: verificar que DataTable não tem borda própria quando dentro de DataCard (seção 10)

---

## 12) Convenções de commit (texto de referência)
- `feat(crud): marca de pneu (t_marcapneu)`
- `fix(auth): bloqueio usuário inativo`
- `fix(ui): alinhamento ícones sidebar colapsada`
- `refactor(legacy): extrai regra vínculo unidade -> serviço`

---

## 13) Glossário rápido (legado)
- `A/I` = ativo/inativo
- `P/F` = pendente/finalizado
- `USU_TIPO == 'A'` = admin (regra comum no legado)
- `USU_CODIGO` = identificador do usuário (padrão do projeto)