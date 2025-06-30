const { createClient } = require('@supabase/supabase-js');

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL!,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!
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