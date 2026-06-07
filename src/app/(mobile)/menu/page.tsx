import type { Viewport } from "next";
import { supabase } from "@/lib/supabase";
import type { Category } from "@/data/menu";
import MenuView from "./MenuView";

export const viewport: Viewport = { themeColor: "#eae6dd" };
export const revalidate = 60;

async function getCategories(): Promise<Category[]> {
  const { data: cats, error: catsError } = await supabase
    .from("categories")
    .select("id, title, sort_order")
    .order("sort_order");

  if (catsError || !cats?.length) return [];

  const { data: dishes, error: dishesError } = await supabase
    .from("dishes")
    .select(
      "id, category_id, name, description, price, weight, photo, badge, kcal, protein, fat, carbs, composition, allergens, sort_order",
    )
    .order("sort_order");

  if (dishesError) return [];

  return cats.map((c) => ({
    id: c.id,
    title: c.title,
    index: String(c.sort_order).padStart(2, "0"),
    dishes: (dishes ?? [])
      .filter((d) => d.category_id === c.id)
      .map((d) => ({
        name: d.name,
        desc: d.description ?? "",
        price: d.price,
        weight: d.weight ?? "",
        photo: d.photo ?? "",
        badge: d.badge ?? undefined,
        kcal: d.kcal ?? undefined,
        protein: d.protein ?? undefined,
        fat: d.fat ?? undefined,
        carbs: d.carbs ?? undefined,
        composition: d.composition ?? undefined,
        allergens: d.allergens ?? undefined,
      })),
  }));
}

export default async function MenuPage() {
  const categories = await getCategories();
  return <MenuView categories={categories} />;
}
