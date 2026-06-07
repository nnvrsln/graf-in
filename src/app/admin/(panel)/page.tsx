import Link from "next/link";
import Image from "next/image";
import { supabaseAdmin } from "@/lib/supabase-admin";
import { cardImg } from "@/data/menu";

export const revalidate = 0;

function StatCard({
  label,
  value,
  sub,
}: {
  label: string;
  value: string | number;
  sub?: string;
}) {
  return (
    <div className="rounded-[14px] border border-black/[0.06] bg-white p-5 shadow-[0_2px_10px_rgba(33,29,23,0.04)]">
      <p className="text-[10px] uppercase tracking-[0.18em] text-brass">{label}</p>
      <p className="mt-2 font-display text-[32px] font-bold leading-none text-ink">
        {value}
      </p>
      {sub && <p className="mt-1.5 text-[12px] text-muted">{sub}</p>}
    </div>
  );
}

export default async function DashboardPage() {
  const [{ data: cats }, { data: dishes }] = await Promise.all([
    supabaseAdmin.from("categories").select("id, title, sort_order").order("sort_order"),
    supabaseAdmin
      .from("dishes")
      .select("id, name, price, photo, category_id")
      .order("sort_order"),
  ]);

  const allDishes = dishes ?? [];
  const allCats = cats ?? [];

  const total = allDishes.length;

  // Считаем блюда по категориям
  const byCategory = allCats.map((c) => ({
    ...c,
    count: allDishes.filter((d) => d.category_id === c.id).length,
  }));
  const maxCount = Math.max(1, ...byCategory.map((c) => c.count));

  const recent = [...allDishes].slice(-4).reverse();

  return (
    <div>
      {/* Заголовок */}
      <div className="mb-7">
        <p className="text-[11px] uppercase tracking-[0.22em] text-brass">
          Добро пожаловать
        </p>
        <h1 className="mt-1.5 font-display text-[30px] font-bold leading-none text-ink">
          Управление меню
        </h1>
      </div>

      {/* Статистика */}
      <div className="grid grid-cols-2 gap-4">
        <StatCard label="Блюд в меню" value={total} />
        <StatCard label="Категорий" value={allCats.length} />
      </div>

      <div className="mt-6 grid gap-6 lg:grid-cols-[1.4fr_1fr]">
        {/* Распределение по категориям */}
        <section className="rounded-[14px] border border-black/[0.06] bg-white p-6 shadow-[0_2px_10px_rgba(33,29,23,0.04)]">
          <div className="mb-5 flex items-center justify-between">
            <h2 className="font-display text-[18px] font-bold text-ink">
              По категориям
            </h2>
            <Link
              href="/admin/categories"
              className="text-[12.5px] text-brass transition-colors hover:text-gold"
            >
              Управлять →
            </Link>
          </div>

          {byCategory.length === 0 && (
            <p className="text-[13px] text-muted">Категорий пока нет.</p>
          )}

          <div className="space-y-3.5">
            {byCategory.map((c) => (
              <div key={c.id}>
                <div className="mb-1.5 flex items-baseline justify-between">
                  <span className="text-[13.5px] font-medium text-ink">
                    {c.title}
                  </span>
                  <span className="text-[12px] tabular-nums text-muted">
                    {c.count} {c.count === 1 ? "блюдо" : "блюд"}
                  </span>
                </div>
                <div className="h-1.5 overflow-hidden rounded-full bg-paper-2">
                  <div
                    className="h-full rounded-full bg-gradient-to-r from-brass to-gold transition-all"
                    style={{ width: `${(c.count / maxCount) * 100}%` }}
                  />
                </div>
              </div>
            ))}
          </div>
        </section>

        {/* Быстрые действия */}
        <section className="rounded-[14px] border border-black/[0.06] bg-white p-6 shadow-[0_2px_10px_rgba(33,29,23,0.04)]">
          <h2 className="mb-5 font-display text-[18px] font-bold text-ink">
            Быстрые действия
          </h2>
          <div className="space-y-2.5">
            <Link
              href="/admin/dishes/new"
              className="flex items-center gap-3 rounded-[10px] bg-ink px-4 py-3 text-[13.5px] font-semibold text-paper transition-opacity hover:opacity-90"
            >
              <span className="text-[18px] leading-none">+</span>
              Добавить блюдо
            </Link>
            <Link
              href="/admin/categories"
              className="flex items-center gap-3 rounded-[10px] border border-black/10 px-4 py-3 text-[13.5px] font-medium text-ink transition-colors hover:border-brass/40 hover:bg-paper/40"
            >
              Новая категория
            </Link>
            <Link
              href="/admin/settings"
              className="flex items-center gap-3 rounded-[10px] border border-black/10 px-4 py-3 text-[13.5px] font-medium text-ink transition-colors hover:border-brass/40 hover:bg-paper/40"
            >
              Настройки ресторана
            </Link>
          </div>
        </section>
      </div>

      {/* Недавние блюда */}
      <section className="mt-6 rounded-[14px] border border-black/[0.06] bg-white p-6 shadow-[0_2px_10px_rgba(33,29,23,0.04)]">
        <div className="mb-5 flex items-center justify-between">
          <h2 className="font-display text-[18px] font-bold text-ink">
            Недавно добавленные
          </h2>
          <Link
            href="/admin/dishes"
            className="text-[12.5px] text-brass transition-colors hover:text-gold"
          >
            Все блюда →
          </Link>
        </div>

        {recent.length === 0 ? (
          <p className="text-[13px] text-muted">Блюд пока нет.</p>
        ) : (
          <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
            {recent.map((d) => (
              <Link
                key={d.id}
                href={`/admin/dishes/${d.id}/edit`}
                className="group overflow-hidden rounded-[11px] border border-black/[0.06] transition-shadow hover:shadow-[0_6px_18px_rgba(33,29,23,0.1)]"
              >
                <div className="relative aspect-square bg-paper-2">
                  {d.photo && (
                    <Image
                      src={cardImg(d.photo)}
                      alt={d.name}
                      fill
                      sizes="160px"
                      className="object-cover"
                    />
                  )}
                </div>
                <div className="p-2.5">
                  <p className="truncate text-[12.5px] font-medium text-ink">
                    {d.name}
                  </p>
                  <p className="mt-0.5 text-[11.5px] tabular-nums text-muted">
                    {d.price}
                  </p>
                </div>
              </Link>
            ))}
          </div>
        )}
      </section>
    </div>
  );
}
