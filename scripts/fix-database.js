require('dotenv').config({ path: '.env.local' });
const { createClient } = require('@supabase/supabase-js');
const fs = require('fs');
const path = require('path');

async function fixDatabaseStructure() {
  try {
    console.log('🔄 Iniciando correção da estrutura do banco de dados...');

    const supabase = createClient(
      process.env.NEXT_PUBLIC_SUPABASE_URL,
      process.env.SUPABASE_SERVICE_ROLE_KEY
    );

    // Ler o script SQL
    const sqlScript = fs.readFileSync(
      path.join(__dirname, 'fix-database-structure.sql'),
      'utf8'
    );

    // Executar o script SQL
    const { error } = await supabase.rpc('exec_sql', { sql_query: sqlScript });

    if (error) {
      console.error('❌ Erro ao executar script de correção:', error);
      return;
    }

    console.log('✅ Estrutura do banco de dados corrigida com sucesso!');
    console.log('📋 Verificações realizadas:');
    console.log('   - Estrutura da tabela cidades padronizada');
    console.log('   - Tabela estados criada/verificada');
    console.log('   - Campos cidade_id e estado_id na tabela acompanhantes');
    console.log('   - Campos corretos na tabela fotos');
    console.log('   - Views atualizadas');
    console.log('   - Índices criados');
    console.log('   - Tipos enumerados verificados');

  } catch (error) {
    console.error('❌ Erro ao executar correção:', error);
  }
}

// Executar se chamado diretamente
if (require.main === module) {
  fixDatabaseStructure();
}

module.exports = { fixDatabaseStructure }; 