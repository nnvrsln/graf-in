"use client";

import { useState, useTransition } from "react";
import Link from "next/link";
import Image from "next/image";
import { cardImg, type Badge } from "@/data/menu";
import DeleteButton from "./DeleteButton";
import { reorderDishes } from "./actions";

const badgeMeta: Record<Badge, { label: string; cls: string }> = {
  hit: { label: "Хит", cls: "bg-brass/15 text-[#8a6a32]" },
  hot: { label: "Остро", cls: "bg-[#b0482a]/10 text-[#a8502f]" },
  veg: { label: "Веган", cls: "bg-[#6e8c42]/12 text-[#5b7a32]" },
};

type Dish = {
  id: string;
  name: string;
  price: string;
  weight: string | null;
  photo: string | null;
  badge: string | null;
  category_id: string;
  sort_order: number;
};
type Cat = { id: string; title: string };

export default function DishesList({
  cats,
  dishes: initial,
}: {
  cats: Cat[];
  dishes: Dish[];
}) {
  const [dishes, setDishes] = useState<Dish[]>(initial);
  const [, startTransition] = useTransition();

  function move(catId: string, index: number, dir: -1 | 1) {
    const list = dishes.filter((d) => d.category_id === catId);
    const target = index + dir;
    if (target < 0 || target >= list.length) return;
    [list[index], list[target]] = [list[target], list[index]];
    // Собираем новый общий список: блюда этой категории в новом порядке, остальные как есть
    const others = dishes.filter((d) => d.category_id !== catId);
    setDishes([...others, ...list]);
    startTransition(() => {
      reorderDishes(list.map((d) => d.id));
    });
  }

  return (
    <div className="space-y-8">
      {cats.map((c) => {
        const list = dishes.filter((d) => d.category_id === c.id);
        if (list.length === 0) return null;
        return (
          <section key={c.id}>
            <div className="mb-3 flex items-center gap-3">
              <h2 className="font-display text-[18px] font-bold text-ink">
                {c.title}
              </h2>
              <span className="text-[12px] tabular-nums text-muted">
                {list.length}
              </span>
              <span className="h-px flex-1 bg-black/[0.07]" />
            </div>

            <div className="space-y-2">
              {list.map((d, i) => (
                <div
                  key={d.id}
                  className="flex items-center gap-3 rounded-[12px] border border-black/[0.06] bg-white px-3 py-2.5 shadow-[0_1px_4px_rgba(33,29,23,0.04)] transition-shadow hover:shadow-[0_3px_12px_rgba(33,29,23,0.08)]"
                >
                  {/* Стрелки порядка */}
                  <div className="flex shrink-0 flex-col">
                    <button
                      onClick={() => move(c.id, i, -1)}
                      disabled={i === 0}
                      className="flex h-5 w-6 items-center justify-center rounded-[5px] text-muted transition-colors hover:bg-paper hover:text-brass disabled:opacity-25 disabled:hover:bg-transparent"
                      aria-label="Выше"
                    >
                      <svg viewBox="0 0 24 24" className="h-3.5 w-3.5 fill-current" aria-hidden>
                        <path d="M7 14l5-5 5 5z" />
                      </svg>
                    </button>
                    <button
                      onClick={() => move(c.id, i, 1)}
                      disabled={i === list.length - 1}
                      className="flex h-5 w-6 items-center justify-center rounded-[5px] text-muted transition-colors hover:bg-paper hover:text-brass disabled:opacity-25 disabled:hover:bg-transparent"
                      aria-label="Ниже"
                    >
                      <svg viewBox="0 0 24 24" className="h-3.5 w-3.5 fill-current" aria-hidden>
                        <path d="M7 10l5 5 5-5z" />
                      </svg>
                    </button>
                  </div>

                  {/* Фото */}
                  <div className="relative h-14 w-14 shrink-0 overflow-hidden rounded-[8px] bg-paper-2">
                    {d.photo ? (
                      <Image
                        src={cardImg(d.photo)}
                        alt={d.name}
                        fill
                        sizes="56px"
                        className="object-cover"
                      />
                    ) : (
                      <div className="flex h-full w-full items-center justify-center text-muted/40">
                        <svg viewBox="0 0 24 24" className="h-6 w-6 fill-current" aria-hidden>
                          <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z" />
                        </svg>
                      </div>
                    )}
                  </div>

                  {/* Инфо */}
                  <div className="min-w-0 flex-1">
                    <div className="flex items-center gap-2">
                      <p className="truncate font-display text-[15.5px] font-semibold text-ink">
                        {d.name}
                      </p>
                      {d.badge && badgeMeta[d.badge as Badge] && (
                        <span
                          className={`shrink-0 rounded-full px-2 py-[2px] text-[8px] font-bold uppercase tracking-[0.12em] ${badgeMeta[d.badge as Badge].cls}`}
                        >
                          {badgeMeta[d.badge as Badge].label}
                        </span>
                      )}
                    </div>
                    {d.weight && (
                      <p className="mt-0.5 text-[11.5px] text-muted">{d.weight}</p>
                    )}
                  </div>

                  {/* Цена */}
                  <span className="shrink-0 text-[14px] font-bold tabular-nums text-ink">
                    {d.price}
                  </span>

                  {/* Действия */}
                  <div className="flex shrink-0 items-center gap-1">
                    <Link
                      href={`/admin/dishes/${d.id}/edit`}
                      className="flex h-8 w-8 items-center justify-center rounded-[7px] text-muted transition-colors hover:bg-paper hover:text-brass"
                      aria-label="Редактировать"
                    >
                      <svg viewBox="0 0 24 24" className="h-[15px] w-[15px] fill-current" aria-hidden>
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34a.9959.9959 0 00-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
                      </svg>
                    </Link>
                    <DeleteButton id={d.id} name={d.name} />
                  </div>
                </div>
              ))}
            </div>
          </section>
        );
      })}
    </div>
  );
}
