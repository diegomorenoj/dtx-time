# DTX Monorepo

Este es el monorepo que contiene tanto la aplicación frontend como el backend API del proyecto DTX, migrados desde GitLab.

## Estructura del proyecto

```
dtx-time/
├── apps/
│   ├── dtx-app/     # Aplicación frontend (migrada desde dtx-app/dev)
│   └── dtx-api/     # API backend (migrada desde dtx-api/dev)
└── README.md
```

## Apps incluidas

- **dtx-app**: Aplicación frontend del proyecto DTX
- **dtx-api**: API backend del proyecto DTX

## Historial de migración

Este monorepo fue creado mediante la combinación de dos repositorios independientes de GitLab:
- dtx-app (rama dev) → apps/dtx-app
- dtx-api (rama dev) → apps/dtx-api

Todo el historial de commits de ambos repositorios se ha preservado en este monorepo.
