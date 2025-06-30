import React from 'react';

export default function FloatAdminButton() {
  return (
    <a
      href="https://wa.me/5547996829294"
      target="_blank"
      rel="noopener noreferrer"
      className="fixed bottom-6 right-6 z-50 flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-5 rounded-full shadow-lg transition-colors text-lg"
      title="Falar com a administração"
    >
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-6 h-6">
        <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 12c0 5.385 4.365 9.75 9.75 9.75 1.7 0 3.3-.425 4.7-1.225l3.025.8a1.125 1.125 0 0 0 1.375-1.375l-.8-3.025A9.708 9.708 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12Z" />
        <path strokeLinecap="round" strokeLinejoin="round" d="M8.625 10.125c.375 1.125 1.5 2.25 2.625 2.625m0 0c.375.125.75.25 1.125.25.375 0 .75-.125 1.125-.25m-2.25 0c.375.125.75.25 1.125.25.375 0 .75-.125 1.125-.25" />
      </svg>
      Falar com Admin
    </a>
  );
} 