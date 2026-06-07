import Link from "next/link";
import { notFound } from "next/navigation";
import { supabaseAdmin } from "@/lib/supabase-admin";
import DishForm from "../../DishForm";

export const revalidate = 0;

export default async function EditDishPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;

  const [{ data: dish }, { data: cats }] = await Promise.all([
    supabaseAdmin.from("dishes").select("*").eq("id", id).single(),
    supabaseAdmin.from("categories").select("id, title").order("sort_order"),
  ]);

  if (!dish) notFound();

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
          {dish.name}
        </h1>
      </div>
      <DishForm
        dish={{
          id: dish.id,
          name: dish.name,
          description: dish.description ?? "",
          price: dish.price,
          weight: dish.weight ?? "",
          category_id: dish.category_id,
          badge: dish.badge ?? "",
          photo: dish.photo ?? "",
          kcal: dish.kcal ?? undefined,
          protein: dish.protein ?? undefined,
          fat: dish.fat ?? undefined,
          carbs: dish.carbs ?? undefined,
          composition: dish.composition ?? "",
          allergens: dish.allergens ?? [],
          sort_order: dish.sort_order ?? 0,
        }}
        categories={cats ?? []}
      />
    </div>
  );
}
