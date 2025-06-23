const { createClient } = require('@supabase/supabase-js');

const supabase = createClient(
  'https://qfxnenbanimljqjfybva.supabase.co',
  'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InFmeG5lbmJhbmltbGpxamZ5YnZhIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc0OTkwODcwMCwiZXhwIjoyMDY1NDg0NzAwfQ.MfWDLp53YIEpJgDpmabLoxF7_ZPM5LxEvH18mzxmNOc'
);

async function resetPassword() {
  const { data, error } = await supabase.auth.admin.updateUserById(
    '8fa5fa27-fcee-49fb-9980-96bca05e39ac',
    { password: 'admin_sigilos@s' }
  );

  if (error) {
    console.error('Erro ao alterar senha:', error);
  } else {
    console.log('Senha alterada com sucesso!');
  }
}

resetPassword();