import { supabaseAdmin } from "@/lib/supabase-admin";
import CategoriesClient from "./CategoriesClient";

export const revalidate = 0;

export default async function CategoriesPage() {
  const [{ data: cats }, { data: dishes }] = await Promise.all([
    supabaseAdmin.from("categories").select("id, title, sort_order").order("sort_order"),
    supabaseAdmin.from("dishes").select("category_id"),
  ]);

  const counts: Record<string, number> = {};
  for (const d of dishes ?? []) {
    counts[d.category_id] = (counts[d.category_id] ?? 0) + 1;
  }

  const withCounts = (cats ?? []).map((c) => ({
    ...c,
    count: counts[c.id] ?? 0,
  }));

  return (
    <div>
      <div className="mb-7">
        <p className="text-[11px] uppercase tracking-[0.22em] text-brass">Меню</p>
        <h1 className="mt-1.5 font-display text-[30px] font-bold leading-none text-ink">
          Категории
        </h1>
      </div>
      <CategoriesClient initialCategories={withCounts} />
    </div>
  );
}
