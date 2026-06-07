"use server";

import { revalidatePath } from "next/cache";
import { supabaseAdmin } from "@/lib/supabase-admin";

// Транслитерация кириллицы для генерации ID категории
const translitMap: Record<string, string> = {
  а: "a", б: "b", в: "v", г: "g", д: "d", е: "e", ё: "e", ж: "zh",
  з: "z", и: "i", й: "y", к: "k", л: "l", м: "m", н: "n", о: "o",
  п: "p", р: "r", с: "s", т: "t", у: "u", ф: "f", х: "h", ц: "ts",
  ч: "ch", ш: "sh", щ: "sch", ъ: "", ы: "y", ь: "", э: "e", ю: "yu", я: "ya",
};

function makeId(title: string): string {
  const base = title
    .toLowerCase()
    .split("")
    .map((ch) => translitMap[ch] ?? ch)
    .join("")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");
  return base || `cat-${Date.now()}`;
}

export async function createCategory(formData: FormData) {
  const title = (formData.get("title") as string)?.trim();
  if (!title) return { error: "Введите название" };

  const { data: existing } = await supabaseAdmin
    .from("categories")
    .select("id, sort_order");

  const taken = new Set((existing ?? []).map((c) => c.id));
  let id = makeId(title);
  if (taken.has(id)) id = `${id}-${Date.now().toString().slice(-4)}`;

  const maxOrder = Math.max(0, ...(existing ?? []).map((c) => c.sort_order ?? 0));

  const { error } = await supabaseAdmin
    .from("categories")
    .insert({ id, title, sort_order: maxOrder + 1 });

  if (error) return { error: "Не удалось создать категорию" };

  revalidatePath("/menu");
  revalidatePath("/admin");
  return { ok: true, id };
}

export async function updateCategory(formData: FormData) {
  const id = formData.get("id") as string;
  const title = formData.get("title") as string;

  await supabaseAdmin.from("categories").update({ title }).eq("id", id);
  revalidatePath("/menu");
  revalidatePath("/admin");
  return { ok: true };
}

export async function deleteCategory(id: string) {
  await supabaseAdmin.from("categories").delete().eq("id", id);
  revalidatePath("/menu");
  revalidatePath("/admin");
}

// Переупорядочивание: проставляем sort_order по позиции в массиве
export async function reorderCategories(orderedIds: string[]) {
  await Promise.all(
    orderedIds.map((id, i) =>
      supabaseAdmin.from("categories").update({ sort_order: i + 1 }).eq("id", id),
    ),
  );
  revalidatePath("/menu");
  revalidatePath("/admin");
}
