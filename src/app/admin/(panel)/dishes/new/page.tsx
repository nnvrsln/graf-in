import Link from "next/link";
import { supabaseAdmin } from "@/lib/supabase-admin";
import DishForm from "../DishForm";

export const revalidate = 0;

export default async function NewDishPage() {
  const { data: cats } = await supabaseAdmin
    .from("categories")
    .select("id, title")
    .order("sort_order");

  return (
    <div>
      <div className="mb-6">
        <Link
          href="/admin/dishes"
          className="text-[12.5px] text-muted transition-colors hover:text-ink"
        >
          ← Все блюда
        </Link>
        <h1 className="mt-2 font-display text-[28px] font-bold leading-none text-ink">
          Новое блюдо
        </h1>
      </div>
      <DishForm categories={cats ?? []} />
    </div>
  );
}
