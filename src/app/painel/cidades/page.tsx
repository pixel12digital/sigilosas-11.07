'use client';

import { useState, useEffect } from 'react';

const ESTADOS = [
  { uf: 'AC', nome: 'Acre' },
  { uf: 'AL', nome: 'Alagoas' },
  { uf: 'AP', nome: 'Amapá' },
  { uf: 'AM', nome: 'Amazonas' },
  { uf: 'BA', nome: 'Bahia' },
  { uf: 'CE', nome: 'Ceará' },
  { uf: 'DF', nome: 'Distrito Federal' },
  { uf: 'ES', nome: 'Espírito Santo' },
  { uf: 'GO', nome: 'Goiás' },
  { uf: 'MA', nome: 'Maranhão' },
  { uf: 'MT', nome: 'Mato Grosso' },
  { uf: 'MS', nome: 'Mato Grosso do Sul' },
  { uf: 'MG', nome: 'Minas Gerais' },
  { uf: 'PA', nome: 'Pará' },
  { uf: 'PB', nome: 'Paraíba' },
  { uf: 'PR', nome: 'Paraná' },
  { uf: 'PE', nome: 'Pernambuco' },
  { uf: 'PI', nome: 'Piauí' },
  { uf: 'RJ', nome: 'Rio de Janeiro' },
  { uf: 'RN', nome: 'Rio Grande do Norte' },
  { uf: 'RS', nome: 'Rio Grande do Sul' },
  { uf: 'RO', nome: 'Rondônia' },
  { uf: 'RR', nome: 'Roraima' },
  { uf: 'SC', nome: 'Santa Catarina' },
  { uf: 'SP', nome: 'São Paulo' },
  { uf: 'SE', nome: 'Sergipe' },
  { uf: 'TO', nome: 'Tocantins' }
];

interface Cidade {
  id: string;
  nome: string;
  estado: string;
}

export default function CidadesPage() {
  const [cidade, setCidade] = useState('');
  const [estado, setEstado] = useState('');
  const [cidades, setCidades] = useState<Cidade[]>([]);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(false);

  const getNomeEstado = (uf: string) => {
    const estado = ESTADOS.find(e => e.uf === uf);
    return estado ? estado.nome : uf;
  };

  useEffect(() => {
    loadCidades();
  }, []);

  const loadCidades = async () => {
    try {
      const response = await fetch('/api/cidades/listar');
      const data = await response.json();
      if (data.success) {
        setCidades(data.cidades);
      }
    } catch (error) {
      console.error('Erro ao carregar cidades:', error);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);

    try {
      const response = await fetch('/api/cidades/cadastrar', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ cidade, estado }),
      });

      const data = await response.json();

      if (data.success) {
        setSuccess('Cidade cadastrada com sucesso!');
        setCidade('');
        setEstado('');
        loadCidades();
      } else {
        setError(data.error || 'Erro ao cadastrar cidade');
      }
    } catch (error) {
      setError('Erro ao cadastrar cidade');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id: string) => {
    if (!window.confirm('Tem certeza de que deseja excluir esta cidade?')) {
      return;
    }
    setError('');
    setSuccess('');

    try {
      const response = await fetch('/api/cidades/deletar', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id }),
      });
      const data = await response.json();
      if (data.success) {
        setSuccess('Cidade excluída com sucesso!');
        loadCidades();
      } else {
        setError(data.error || 'Erro ao excluir a cidade');
      }
    } catch (err) {
      setError('Ocorreu um erro ao excluir a cidade.');
    }
  };

  return (
    <div className="p-6">
      <h1 className="text-2xl font-bold mb-6">Cadastro de Cidades</h1>

      {error && (
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          {error}
        </div>
      )}

      {success && (
        <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
          {success}
        </div>
      )}

      <div className="space-y-8">
        <div className="bg-white p-6 rounded-lg shadow">
          <h2 className="text-xl font-semibold mb-4">Nova Cidade</h2>
          <form onSubmit={handleSubmit}>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label className="block text-gray-700 text-sm font-bold mb-2">
                  Nome da Cidade
                </label>
                <input
                  type="text"
                  value={cidade}
                  onChange={(e) => setCidade(e.target.value)}
                  className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  required
                />
              </div>
              <div>
                <label className="block text-gray-700 text-sm font-bold mb-2">
                  Estado
                </label>
                <select
                  value={estado}
                  onChange={(e) => setEstado(e.target.value)}
                  className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  required
                >
                  <option value="">Selecione um estado</option>
                  {ESTADOS.map((estado) => (
                    <option key={estado.uf} value={estado.uf}>
                      {estado.nome} ({estado.uf})
                    </option>
                  ))}
                </select>
              </div>
              <div className="flex items-end">
                <button
                  type="submit"
                  disabled={loading}
                  className={`bg-purple-600 text-white font-bold py-2 px-4 rounded w-full ${
                    loading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-purple-700'
                  }`}
                >
                  {loading ? 'Cadastrando...' : 'Cadastrar Cidade'}
                </button>
              </div>
            </div>
          </form>
        </div>

        <div className="bg-white p-6 rounded-lg shadow">
          <h2 className="text-xl font-semibold mb-4">Cidades Cadastradas</h2>
          {cidades.length === 0 ? (
            <p className="text-gray-500">Nenhuma cidade cadastrada</p>
          ) : (
            <div className="overflow-x-auto">
              <table className="min-w-full bg-white">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="py-2 px-4 border-b">Cidade</th>
                    <th className="py-2 px-4 border-b">Estado</th>
                    <th className="py-2 px-4 border-b text-right">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  {cidades.map((cidade) => (
                    <tr key={cidade.id}>
                      <td className="py-2 px-4 border-b">{cidade.nome}</td>
                      <td className="py-2 px-4 border-b">{getNomeEstado(cidade.estado)}</td>
                      <td className="py-2 px-4 border-b text-right">
                        <button
                          onClick={() => handleDelete(cidade.id)}
                          className="text-red-500 hover:text-red-700 font-semibold"
                        >
                          Excluir
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>
    </div>
  );
} 