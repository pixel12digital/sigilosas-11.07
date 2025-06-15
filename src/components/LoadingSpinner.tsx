export default function LoadingSpinner() {
  return (
    <div className="flex items-center justify-center min-h-[400px]">
      <div className="text-center">
        <div className="spinner mx-auto mb-4"></div>
        <p className="text-[#2E1530] text-lg">Carregando...</p>
      </div>
    </div>
  );
} 