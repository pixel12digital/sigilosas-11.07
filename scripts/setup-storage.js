require('dotenv').config({ path: '.env.local' });
const { createClient } = require('@supabase/supabase-js');
const fs = require('fs');
const path = require('path');

// Configurações do Supabase
const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL;
const supabaseServiceKey = process.env.SUPABASE_SERVICE_ROLE_KEY;

if (!supabaseUrl || !supabaseServiceKey) {
  console.error('❌ Variáveis de ambiente NEXT_PUBLIC_SUPABASE_URL e SUPABASE_SERVICE_ROLE_KEY são obrigatórias');
  process.exit(1);
}

const supabase = createClient(supabaseUrl, supabaseServiceKey);

async function setupStorage() {
  try {
    console.log('🔄 Iniciando configuração do storage...');

    // Criar buckets
    const buckets = [
      { id: 'images', name: 'images', public: true },
      { id: 'documents', name: 'documents', public: false },
      { id: 'videos', name: 'videos', public: false }
    ];

    for (const bucket of buckets) {
      const { error } = await supabase.storage.createBucket(bucket.id, {
        public: bucket.public,
        fileSizeLimit: bucket.id === 'videos' ? 104857600 : 10485760 // 100MB para vídeos, 10MB para outros
      });

      if (error) {
        if (error.message.includes('already exists')) {
          console.log(`ℹ️ Bucket ${bucket.id} já existe`);
        } else {
          console.error(`❌ Erro ao criar bucket ${bucket.id}:`, error);
        }
      } else {
        console.log(`✅ Bucket ${bucket.id} criado com sucesso`);
      }
    }

    // Criar estrutura de pastas
    const folders = [
      'images/acompanhantes',
      'images/blog',
      'images/banners',
      'documents/acompanhantes',
      'videos/acompanhantes'
    ];

    for (const folder of folders) {
      const [bucket, ...path] = folder.split('/');
      const { error } = await supabase.storage
        .from(bucket)
        .upload(`${path.join('/')}/.keep`, new Uint8Array(0));

      if (error && !error.message.includes('already exists')) {
        console.error(`❌ Erro ao criar pasta ${folder}:`, error);
      } else {
        console.log(`✅ Pasta ${folder} criada/verificada com sucesso`);
      }
    }

    console.log('🎉 Configuração do storage concluída!');

  } catch (error) {
    console.error('❌ Erro durante a configuração:', error);
    process.exit(1);
  }
}

// Executar se chamado diretamente
if (require.main === module) {
  setupStorage();
}

module.exports = { setupStorage }; 