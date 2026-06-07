import { login } from "./actions";

// Логотип «Графин» (фиолетовый SVG → перекрашиваем в бумагу через mask)
const logoMask = {
  aspectRatio: "429 / 191",
  WebkitMaskImage: "url(/logo.svg)",
  maskImage: "url(/logo.svg)",
  WebkitMaskRepeat: "no-repeat",
  maskRepeat: "no-repeat",
  WebkitMaskSize: "contain",
  maskSize: "contain",
} as const;

export default async function LoginPage({
  searchParams,
}: {
  searchParams: Promise<{ error?: string }>;
}) {
  const { error } = await searchParams;

  return (
    <div className="relative flex min-h-dvh items-center justify-center overflow-hidden bg-dark px-6">
      {/* Тёплое золотое свечение */}
      <div
        aria-hidden
        className="pointer-events-none absolute left-1/2 top-[38%] h-72 w-[420px] max-w-[90%] -translate-x-1/2 -translate-y-1/2 blur-[80px]"
        style={{
          background:
            "radial-gradient(ellipse at center, rgba(216,184,119,0.32) 0%, rgba(216,184,119,0.10) 45%, transparent 72%)",
        }}
      />
      {/* Виньетка по краям */}
      <div
        aria-hidden
        className="pointer-events-none absolute inset-0"
        style={{
          background:
            "radial-gradient(ellipse at 50% 35%, transparent 30%, rgba(0,0,0,0.45) 100%)",
        }}
      />

      <div className="relative z-10 w-full max-w-[340px]">
        {/* Логотип + подпись */}
        <div className="mb-10 flex flex-col items-center">
          <div
            role="img"
            aria-label="Графин"
            className="w-[180px] bg-paper"
            style={logoMask}
          />
          <div className="mt-5 flex items-center gap-3">
            <span className="h-px w-8 bg-gold/40" />
            <span className="text-[10px] uppercase tracking-[0.32em] text-gold/90">
              Панель управления
            </span>
            <span className="h-px w-8 bg-gold/40" />
          </div>
        </div>

        {/* Форма */}
        <form action={login} className="flex flex-col">
          <label className="block">
            <span className="mb-2 block text-[10px] uppercase tracking-[0.22em] text-paper/55">
              Пароль доступа
            </span>
            <input
              type="password"
              name="password"
              required
              autoComplete="current-password"
              placeholder="••••••••••"
              className="w-full rounded-[6px] border border-paper/15 bg-paper/[0.04] px-4 py-3.5 text-[15px] tracking-wide text-paper outline-none transition placeholder:text-paper/25 focus:border-gold/55 focus:bg-paper/[0.07] focus:ring-2 focus:ring-gold/15"
            />
          </label>

          {error && (
            <p className="mt-3 flex items-center gap-1.5 text-[12.5px] text-[#d98c6a]">
              <svg viewBox="0 0 24 24" className="h-3.5 w-3.5 fill-current" aria-hidden>
                <path d="M12 2L1 21h22L12 2zm0 5.5c.55 0 1 .45 1 1V14c0 .55-.45 1-1 1s-1-.45-1-1V8.5c0-.55.45-1 1-1zM12 16.5c.69 0 1.25.56 1.25 1.25S12.69 19 12 19s-1.25-.56-1.25-1.25S11.31 16.5 12 16.5z" />
              </svg>
              Неверный пароль. Попробуйте ещё раз.
            </p>
          )}

          <button
            type="submit"
            className="group mt-7 flex items-center justify-center gap-2.5 rounded-[5px] border border-gold/55 py-[15px] text-[11px] font-semibold uppercase tracking-[0.26em] text-gold shadow-[0_12px_34px_-18px_rgba(216,184,119,0.4)] transition-colors duration-200 hover:bg-gold/10"
          >
            Войти
            <span
              aria-hidden
              className="transition-transform duration-200 group-hover:translate-x-1"
            >
              →
            </span>
          </button>
        </form>

        {/* Подвал */}
        <p className="mt-10 text-center text-[10px] uppercase tracking-[0.2em] text-paper/30">
          Графин · Авторская кухня
        </p>
      </div>
    </div>
  );
}
