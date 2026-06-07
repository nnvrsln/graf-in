import Link from "next/link";
import AdminNav from "./AdminNav";
import { logout } from "../login/actions";

// Знак-кристалл (белый SVG) → латунь через mask
const znakMask = {
  aspectRatio: "230 / 374",
  WebkitMaskImage: "url(/znak.svg)",
  maskImage: "url(/znak.svg)",
  WebkitMaskRepeat: "no-repeat",
  maskRepeat: "no-repeat",
  WebkitMaskSize: "contain",
  maskSize: "contain",
} as const;

export default function AdminLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <div className="min-h-dvh overflow-x-hidden bg-[#f4f1ea]">
      {/* Тёмный топбар */}
      <header className="sticky top-0 z-30 border-b border-black/30 bg-dark">
        <div className="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-5">
          {/* Бренд */}
          <Link href="/admin" className="flex items-center gap-3">
            <div
              role="img"
              aria-label="Графин"
              className="h-7 bg-gold"
              style={znakMask}
            />
            <div className="flex flex-col leading-none">
              <span className="font-display text-[19px] font-bold text-paper">
                Графин
              </span>
              <span className="mt-0.5 text-[8px] uppercase tracking-[0.24em] text-gold/80">
                Панель управления
              </span>
            </div>
          </Link>

          {/* Навигация — по центру */}
          <div className="hidden md:block">
            <AdminNav />
          </div>

          {/* Действия */}
          <div className="flex items-center gap-2">
            <Link
              href="/"
              target="_blank"
              className="hidden items-center gap-1.5 rounded-[7px] border border-paper/15 px-3 py-2 text-[12.5px] text-paper/70 transition-colors hover:border-paper/30 hover:text-paper sm:flex"
            >
              Открыть сайт
              <svg viewBox="0 0 24 24" className="h-3.5 w-3.5 fill-current" aria-hidden>
                <path d="M14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7zM5 5h5V3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-5h-2v5H5V5z" />
              </svg>
            </Link>
            <form action={logout}>
              <button
                type="submit"
                className="flex items-center gap-1.5 rounded-[7px] px-3 py-2 text-[12.5px] text-paper/55 transition-colors hover:text-[#d98c6a]"
              >
                Выйти
                <svg viewBox="0 0 24 24" className="h-4 w-4 fill-current" aria-hidden>
                  <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z" />
                </svg>
              </button>
            </form>
          </div>
        </div>

        {/* Мобильная навигация — горизонтальный скролл внутри себя */}
        <div className="no-scrollbar overflow-x-auto border-t border-paper/10 px-3 py-2 md:hidden">
          <AdminNav />
        </div>
      </header>

      <main className="mx-auto max-w-6xl px-5 py-8">{children}</main>
    </div>
  );
}
