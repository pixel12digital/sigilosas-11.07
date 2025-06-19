#!/bin/bash

# Configurar usuário admin
supabase auth users create \
  --email admin@sigilosas.com.br \
  --password admin123 \
  --data '{"role": "admin"}' \
  --confirm 