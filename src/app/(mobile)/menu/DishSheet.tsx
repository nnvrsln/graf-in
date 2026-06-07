"use client";

import { useEffect } from "react";
import { AnimatePresence, motion, useDragControls } from "framer-motion";
import Image from "next/image";
import { type Badge, type Dish, heroImg } from "@/data/menu";

const badgeMeta: Record<Badge, { label: string; cls: string }> = {
  hit: { label: "Хит", cls: "bg-brass/15 text-[#8a6a32] border-gold/30" },
  hot: { label: "Остро", cls: "bg-[#b0482a]/10 text-[#a8502f] border-[#b0482a]/30" },
  veg: { label: "Веган", cls: "bg-[#6e8c42]/12 text-[#5b7a32] border-[#6e8c42]/30" },
};

function NutritionCell({ value, label }: { value: number; label: string }) {
  return (
    <div className="flex flex-col items-center px-1">
      <span className="font-display text-[19px] font-bold leading-none text-ink">
        {value}
      </span>
      <span className="mt-1.5 text-[8.5px] uppercase tracking-[0.14em] text-[#8f8678]">
        {label}
      </span>
    </div>
  );
}

export default function DishSheet({
  dish,
  onClose,
}: {
  dish: Dish | null;
  onClose: () => void;
}) {
  const controls = useDragControls();

  // Блокируем скролл фона, пока шторка открыта
  useEffect(() => {
    if (!dish) return;
    const prev = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => {
      document.body.style.overflow = prev;
    };
  }, [dish]);

  const hasNutrition =
    dish?.kcal != null ||
    dish?.protein != null ||
    dish?.fat != null ||
    dish?.carbs != null;

  return (
    <AnimatePresence>
      {dish && (
        <>
          {/* Затемнение */}
          <motion.div
            className="fixed inset-0 z-40 bg-dark/55 backdrop-blur-[2px]"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            transition={{ duration: 0.25 }}
            onClick={onClose}
          />

          {/* Шторка */}
          <motion.div
            className="fixed inset-x-0 bottom-0 z-50 mx-auto flex max-h-[92dvh] w-full max-w-[480px] flex-col overflow-hidden rounded-t-[22px] bg-paper shadow-[0_-20px_60px_-20px_rgba(0,0,0,0.5)]"
            initial={{ y: "100%" }}
            animate={{ y: 0 }}
            exit={{ y: "100%" }}
            transition={{ type: "spring", damping: 34, stiffness: 330 }}
            drag="y"
            dragListener={false}
            dragControls={controls}
            dragConstraints={{ top: 0, bottom: 0 }}
            dragElastic={{ top: 0, bottom: 0.5 }}
            onDragEnd={(_, info) => {
              if (info.offset.y > 120 || info.velocity.y > 500) onClose();
            }}
          >
            {/* Ручка для свайпа (поверх фото) */}
            <div
              className="absolute inset-x-0 top-0 z-20 flex cursor-grab touch-none justify-center pt-2.5 active:cursor-grabbing"
              onPointerDown={(e) => controls.start(e)}
            >
              <span className="h-1 w-10 rounded-full bg-white/70" />
            </div>

            {/* Закрыть (поверх фото) */}
            <button
              type="button"
              onClick={onClose}
              aria-label="Закрыть"
              className="absolute right-3.5 top-3.5 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-dark/45 text-[18px] leading-none text-paper backdrop-blur-sm transition-colors hover:bg-dark/65"
            >
              ×
            </button>

            {/* Прокручиваемое содержимое */}
            <div className="overflow-y-auto pb-[calc(env(safe-area-inset-bottom,0px)+1.75rem)]">
              {/* Фото — фон верхней части карточки (во всю ширину) */}
              <div className="relative h-60 w-full">
                <Image
                  src={heroImg(dish.photo)}
                  alt={dish.name}
                  fill
                  sizes="(max-width: 480px) 100vw, 480px"
                  className="object-cover"
                />
                <div className="pointer-events-none absolute inset-x-0 top-0 h-20 bg-gradient-to-b from-black/35 to-transparent" />
              </div>

              <div className="px-6 pt-5">
                {/* Название + цена */}
                <div className="flex items-start justify-between gap-4">
                  <h2 className="font-display text-[24px] font-bold leading-tight text-ink">
                    {dish.name}
                  </h2>
                  <span className="mt-1 whitespace-nowrap text-[18px] font-bold tabular-nums text-ink">
                    {dish.price}
                  </span>
                </div>

                {/* Бейдж + граммовка */}
                <div className="mt-3 flex flex-wrap items-center gap-2.5">
                  {dish.badge && (
                    <span
                      className={`rounded-full border px-2.5 py-[3px] text-[8px] font-bold uppercase tracking-[0.16em] ${badgeMeta[dish.badge].cls}`}
                    >
                      {badgeMeta[dish.badge].label}
                    </span>
                  )}
                  <span className="text-[9px] font-medium uppercase tracking-[0.12em] text-[#8f8678]">
                    {dish.weight}
                  </span>
                </div>

                {/* Описание */}
                <p className="mt-4 text-[13.5px] leading-relaxed text-muted">
                  {dish.desc}
                </p>

                {/* БЖУ */}
                {hasNutrition && (
                  <div className="mt-6">
                    <p className="mb-3 text-[9px] uppercase tracking-[0.26em] text-brass">
                      Пищевая ценность
                    </p>
                    <div className="grid grid-cols-4 divide-x divide-ink/10 rounded-[12px] border border-ink/10 bg-paper-2/50 py-3.5">
                      {dish.kcal != null && (
                        <NutritionCell value={dish.kcal} label="ккал" />
                      )}
                      {dish.protein != null && (
                        <NutritionCell value={dish.protein} label="белки" />
                      )}
                      {dish.fat != null && (
                        <NutritionCell value={dish.fat} label="жиры" />
                      )}
                      {dish.carbs != null && (
                        <NutritionCell value={dish.carbs} label="углев." />
                      )}
                    </div>
                  </div>
                )}

                {/* Состав */}
                {dish.composition && (
                  <div className="mt-6">
                    <p className="mb-2 text-[9px] uppercase tracking-[0.26em] text-brass">
                      Состав
                    </p>
                    <p className="text-[13px] leading-relaxed text-muted">
                      {dish.composition}
                    </p>
                  </div>
                )}

                {/* Аллергены */}
                {dish.allergens && dish.allergens.length > 0 && (
                  <div className="mt-6 rounded-[11px] border border-[#b0482a]/25 bg-[#b0482a]/[0.06] px-3.5 py-3">
                    <div className="flex items-center gap-1.5">
                      <svg
                        viewBox="0 0 24 24"
                        className="h-[13px] w-[13px] shrink-0 fill-[#a8502f]"
                        aria-hidden
                      >
                        <path d="M12 2L1 21h22L12 2zm0 5.5c.55 0 1 .45 1 1V14c0 .55-.45 1-1 1s-1-.45-1-1V8.5c0-.55.45-1 1-1zM12 16.5c.69 0 1.25.56 1.25 1.25S12.69 19 12 19s-1.25-.56-1.25-1.25S11.31 16.5 12 16.5z" />
                      </svg>
                      <p className="text-[8.5px] uppercase tracking-[0.2em] text-[#a8502f]">
                        Содержит аллергены
                      </p>
                    </div>
                    <p className="mt-1.5 text-[13px] font-medium leading-snug text-ink/85">
                      {dish.allergens.join(" · ")}
                    </p>
                    <p className="mt-1 text-[11px] leading-snug text-muted">
                      Сообщите официанту при непереносимости.
                    </p>
                  </div>
                )}
              </div>
            </div>
          </motion.div>
        </>
      )}
    </AnimatePresence>
  );
}
