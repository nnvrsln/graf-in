"use client";

import { useEffect, useRef, useState } from "react";
import Image from "next/image";
import Link from "next/link";
import { heroImg, type Badge, type Category, type Dish } from "@/data/menu";
import DishSheet from "./DishSheet";

const badgeMeta: Record<Badge, { label: string; cls: string }> = {
  hit: { label: "Хит", cls: "bg-brass/15 text-[#8a6a32] border-gold/30" },
  hot: { label: "Остро", cls: "bg-[#b0482a]/10 text-[#a8502f] border-[#b0482a]/30" },
  veg: { label: "Веган", cls: "bg-[#6e8c42]/12 text-[#5b7a32] border-[#6e8c42]/30" },
};

// Знак-кристалл (бел. SVG) → перекрашиваем в латунь через mask
const znakMask = {
  aspectRatio: "230 / 374",
  WebkitMaskImage: "url(/znak.svg)",
  maskImage: "url(/znak.svg)",
  WebkitMaskRepeat: "no-repeat",
  maskRepeat: "no-repeat",
  WebkitMaskSize: "contain",
  maskSize: "contain",
} as const;

// Разметка меню. Вынесена отдельно, чтобы использовать и как страницу (/menu),
// и как слой-подложку при переходе с приветствия.
export default function MenuView({ categories }: { categories: Category[] }) {
  const [active, setActive] = useState(categories[0]?.id);
  const [selected, setSelected] = useState<Dish | null>(null);

  // Подсказка «табы скроллятся»: fade-края + лёгкий толчок при загрузке
  const navRef = useRef<HTMLElement | null>(null);
  const [fade, setFade] = useState({ left: false, right: false });

  const updateFade = () => {
    const n = navRef.current;
    if (!n) return;
    const max = n.scrollWidth - n.clientWidth;
    setFade({
      left: n.scrollLeft > 4,
      right: n.scrollLeft < max - 4,
    });
  };

  useEffect(() => {
    const n = navRef.current;
    updateFade();
    window.addEventListener("resize", updateFade);
    // Нативный scroll-слушатель (React onScroll не ловит non-bubbling scroll надёжно)
    n?.addEventListener("scroll", updateFade, { passive: true });

    // Лёгкий «peek»-толчок: показываем, что список можно двигать
    let t1: ReturnType<typeof setTimeout>, t2: ReturnType<typeof setTimeout>;
    if (n && n.scrollWidth > n.clientWidth) {
      t1 = setTimeout(() => n.scrollTo({ left: 28, behavior: "smooth" }), 700);
      t2 = setTimeout(() => n.scrollTo({ left: 0, behavior: "smooth" }), 1150);
    }
    return () => {
      window.removeEventListener("resize", updateFade);
      n?.removeEventListener("scroll", updateFade);
      clearTimeout(t1);
      clearTimeout(t2);
    };
  }, []);

  // Scroll-spy: активная категория = та, что сейчас в зоне под шапкой
  useEffect(() => {
    const sections = categories
      .map((c) => document.getElementById(`cat-${c.id}`))
      .filter((el): el is HTMLElement => el !== null);
    if (sections.length === 0) return;

    const observer = new IntersectionObserver(
      (entries) => {
        for (const e of entries) {
          if (e.isIntersecting) {
            setActive(e.target.id.replace("cat-", ""));
          }
        }
      },
      { rootMargin: "-150px 0px -72% 0px", threshold: 0 },
    );
    sections.forEach((s) => observer.observe(s));
    return () => observer.disconnect();
  }, []);

  // При смене активной категории (скролл-spy) подтягиваем её таб в видимую зону
  useEffect(() => {
    if (!active) return;
    const tab = document.querySelector(`header nav a[href="#cat-${active}"]`);
    tab?.scrollIntoView({ behavior: "smooth", block: "nearest", inline: "center" });
  }, [active]);

  return (
    <>
    <main className="min-h-dvh bg-paper text-ink">
      {/* Шапка: бренд + липкие табы категорий */}
      <header className="sticky top-0 z-20 bg-paper/95 px-5 pt-[calc(env(safe-area-inset-top,0px)+1.1rem)] backdrop-blur">
        {/* Бренд-строка */}
        <div className="relative flex items-center justify-center">
          {/* Назад на приветствие */}
          <Link
            href="/"
            aria-label="На главную"
            className="absolute left-0 flex h-9 w-9 items-center justify-center rounded-full border border-ink/15 text-[15px] text-ink/65 transition-colors hover:bg-ink/5"
          >
            <span aria-hidden>←</span>
          </Link>

          {/* Кристалл-знак (латунь) + «Меню» */}
          <div className="flex items-center gap-2.5">
            <div role="img" aria-label="Графин" className="h-[26px] bg-brass" style={znakMask} />
            <span className="font-display text-[25px] font-bold leading-none text-ink">
              Меню
            </span>
          </div>
        </div>

        {/* Подпись */}
        <p className="mt-2 text-center text-[9px] uppercase tracking-[0.3em] text-brass">
          Авторская кухня
        </p>

        {/* Хайрлайн */}
        <div className="mt-3 h-px bg-ink/10" />

        {/* Табы категорий — горизонтальный скролл от края до края.
            Fade-края подсказывают, что список можно листать. */}
        <div className="relative -mx-5 mt-3">
          {/* Левый fade */}
          <div
            aria-hidden
            className={`pointer-events-none absolute inset-y-0 left-0 z-10 w-8 bg-gradient-to-r from-paper to-transparent transition-opacity duration-200 ${
              fade.left ? "opacity-100" : "opacity-0"
            }`}
          />
          {/* Правый fade + стрелка-намёк */}
          <div
            aria-hidden
            className={`pointer-events-none absolute inset-y-0 right-0 z-10 flex items-center justify-end bg-gradient-to-l from-paper via-paper/90 to-transparent pr-1.5 pb-3 transition-opacity duration-200 ${
              fade.right ? "w-10 opacity-100" : "w-0 opacity-0"
            }`}
          >
            <span className="text-[12px] leading-none text-brass">›</span>
          </div>

        <nav
          ref={navRef}
          onScroll={updateFade}
          className="no-scrollbar flex gap-6 overflow-x-auto px-5 pb-0"
        >
          {categories.map((c) => (
            <a
              key={c.id}
              href={`#cat-${c.id}`}
              onClick={(e) => {
                e.preventDefault();
                setActive(c.id);
                document
                  .getElementById(`cat-${c.id}`)
                  ?.scrollIntoView({ behavior: "smooth", block: "start" });
                history.replaceState(null, "", `#cat-${c.id}`);
                // Подтягиваем активный таб в видимую зону
                (e.currentTarget as HTMLElement).scrollIntoView({
                  behavior: "smooth",
                  block: "nearest",
                  inline: "center",
                });
              }}
              className={`flex-none whitespace-nowrap border-b-[1.5px] pb-3 text-[11px] uppercase tracking-[0.1em] transition-colors ${
                active === c.id
                  ? "border-brass text-ink"
                  : "border-transparent text-[#9a9081]"
              }`}
            >
              {c.title}
            </a>
          ))}
        </nav>
        </div>

        {/* Нижняя линия — того же размера (inset), что и верхняя */}
        <div className="h-px bg-ink/10" />
      </header>

      {categories.map((c) => (
        <section
          key={c.id}
          id={`cat-${c.id}`}
          className="scroll-mt-[calc(env(safe-area-inset-top,0px)+8.5rem)] px-5 pb-1 pt-6"
        >
          <h2 className="mb-4 font-display text-[23px] font-bold leading-none">
            {c.title}
          </h2>

          <div className="flex flex-col gap-7">
            {c.dishes.map((d) => (
              <button
                key={d.name}
                type="button"
                onClick={() => setSelected(d)}
                className="group block w-full text-left transition-opacity active:opacity-80"
              >
                {/* Крупное фото 4:3 на всю ширину */}
                <div className="relative aspect-[4/3] w-full overflow-hidden rounded-[14px] bg-paper-2 shadow-[0_14px_34px_-18px_rgba(33,29,23,0.6)] ring-1 ring-ink/[0.08]">
                  {d.photo ? (
                    <Image
                      src={heroImg(d.photo)}
                      alt={d.name}
                      fill
                      sizes="100vw"
                      className="object-cover transition-transform duration-300 group-active:scale-[1.03]"
                    />
                  ) : (
                    // Плейсхолдер для блюд без фото — латунный знак на бумаге-2
                    <div
                      aria-hidden
                      className="flex h-full w-full items-center justify-center"
                    >
                      <div className="h-[44%] bg-brass/30" style={znakMask} />
                    </div>
                  )}
                  {d.badge && (
                    <span
                      className={`absolute left-3 top-3 rounded-full border px-2.5 py-[4px] text-[8px] font-bold uppercase tracking-[0.16em] backdrop-blur-sm ${badgeMeta[d.badge].cls}`}
                    >
                      {badgeMeta[d.badge].label}
                    </span>
                  )}
                </div>

                {/* Название с точками-выноской до цены */}
                <div className="mt-3 flex items-baseline">
                  <span className="font-display text-[18px] font-semibold leading-tight">
                    {d.name}
                  </span>
                  <span className="mx-2.5 -translate-y-[3px] flex-1 border-b border-dotted border-brass/35" />
                  <span className="whitespace-nowrap text-[16px] font-bold tabular-nums text-ink">
                    {d.price}
                  </span>
                </div>

                <p className="mt-1.5 text-[13px] leading-relaxed text-muted">
                  {d.desc}
                </p>

                <div className="mt-2 text-[9px] font-medium uppercase tracking-[0.12em] text-[#8f8678]">
                  {d.weight}
                </div>
              </button>
            ))}
          </div>
        </section>
      ))}

      <footer className="px-5 pb-12 pt-10 text-center">
        <p className="text-[11px] uppercase tracking-[0.16em] text-muted">
          Графин · Авторская кухня
        </p>
        <p className="mt-4 text-[11px] text-muted">
          Сделано с{" "}
          <svg
            viewBox="0 0 24 24"
            className="inline-block h-[15px] w-[15px] align-[-3px]"
            style={{ filter: "drop-shadow(0 1px 1px rgba(120,80,20,0.35))" }}
            aria-label="любовью"
            role="img"
          >
            <path
              fill="#C2923A"
              d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
            />
          </svg>{" "}
          студией{" "}
          <a
            href="https://nunaev.ru"
            target="_blank"
            rel="noopener noreferrer"
            className="font-semibold text-brass transition-colors hover:text-gold"
          >
            nunaev.ru
          </a>
        </p>
      </footer>
    </main>

    <DishSheet dish={selected} onClose={() => setSelected(null)} />
    </>
  );
}
