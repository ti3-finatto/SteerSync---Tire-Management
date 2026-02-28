# UI SaaS v1

## Estrutura Criada

- Layout autenticado principal:
  - `resources/js/layouts/app-layout.tsx`
  - `resources/js/layouts/app/app-sidebar-layout.tsx`
  - `resources/js/components/app-sidebar.tsx`
  - `resources/js/components/app-sidebar-header.tsx`
- Sidebar existente mantida com todos os itens e rotas.
- Topbar atualizada com busca visual, troca de tema e menu de usuario.
- Componentes de padrao CRUD:
  - `resources/js/components/PageHeader.tsx`
  - `resources/js/components/PageContent.tsx`
  - `resources/js/components/DataCard.tsx`
  - `resources/js/components/DataTable.tsx`

## Logo (Light / Dark)

- Componente unico:
  - `resources/js/components/BrandLogo.tsx`
- Assets:
  - `public/brand/logo-light.png`
  - `public/brand/logo-dark.png`

## Onde Mudar Itens Da Sidebar

- Arquivo:
  - `resources/js/components/app-sidebar.tsx`
- A lista continua organizada por `sections/items`.
- Mantenha `href` atual para preservar navegacao e permissao.

## Tema E Fonte

- Fonte global:
  - `@fontsource/jetbrains-mono` importado em `resources/js/app.tsx`
- Tokens e paleta neutral + lime:
  - `resources/css/app.css`
- Tailwind content/darkMode/fontFamily:
  - `tailwind.config.js`
