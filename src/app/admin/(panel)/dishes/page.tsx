import Link from "next/link";
import { supabaseAdmin } from "@/lib/supabase-admin";
import DishesList from "./DishesList";

export const revalidate = 0;

export default async function DishesPage() {
  const [{ data: cats }, { data: dishes }] = await Promise.all([
    supabaseAdmin.from("categories").select("id, title, sort_order").order("sort_order"),
    supabaseAdmin
      .from("dishes")
      .select("id, name, price, weight, photo, badge, category_id, sort_order")
      .order("category_id")
      .order("sort_order"),
  ]);

  const allCats = cats ?? [];
  const allDishes = dishes ?? [];
  const orphans = allDishes.filter(
    (d) => !allCats.some((c) => c.id === d.category_id),
  );

  return (
    <div>
      {/* Заголовок */}
      <div className="mb-7 flex items-end justify-between">
        <div>
          <p className="text-[11px] uppercase tracking-[0.22em] text-brass">Меню</p>
          <h1 className="mt-1.5 font-display text-[30px] font-bold leading-none text-ink">
            Блюда
            <span className="ml-2.5 text-[16px] font-medium text-muted">
              {allDishes.length}
            </span>
          </h1>
        </div>
        <Link
          href="/admin/dishes/new"
          className="flex items-center gap-2 rounded-[8px] bg-ink px-4 py-2.5 text-[13px] font-semibold text-paper transition-opacity hover:opacity-90"
        >
          <span className="text-[16px] leading-none">+</span>
          Добавить
        </Link>
      </div>

      {allDishes.length === 0 ? (
        <div className="rounded-[14px] border border-dashed border-black/15 bg-white/50 py-16 text-center">
          <p className="text-[14px] text-muted">В меню пока нет блюд.</p>
          <Link
            href="/admin/dishes/new"
            className="mt-3 inline-block text-[13px] font-semibold text-brass hover:text-gold"
          >
            Добавить первое блюдо →
          </Link>
        </div>
      ) : (
        <DishesList cats={allCats} dishes={allDishes} />
      )}

      {/* Блюда без категории (на случай рассинхрона) */}
      {orphans.length > 0 && (
        <section className="mt-8">
          <h2 className="mb-3 font-display text-[18px] font-bold text-[#a8502f]">
            Без категории
          </h2>
          <div className="space-y-2">
            {orphans.map((d) => (
              <div
                key={d.id}
                className="flex items-center gap-4 rounded-[12px] border border-[#b0482a]/20 bg-white px-3 py-2.5"
              >
                <span className="min-w-0 flex-1 truncate font-medium text-ink">
                  {d.name}
                </span>
                <Link
                  href={`/admin/dishes/${d.id}/edit`}
                  className="text-[13px] text-brass hover:text-gold"
                >
                  Исправить →
                </Link>
              </div>
            ))}
          </div>
        </section>
      )}
    </div>
  );
}
