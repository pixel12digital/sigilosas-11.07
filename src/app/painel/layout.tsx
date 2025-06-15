import AdminSidebar from '@/components/AdminSidebar';

export default function PainelLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <div className="min-h-screen bg-[#F8F6F9]">
      <AdminSidebar />
      <main className="ml-16 lg:ml-56 p-4 lg:p-8 min-h-screen transition-all duration-200">
        {children}
      </main>
    </div>
  );
} 