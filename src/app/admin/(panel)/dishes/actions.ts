"use server";

import { revalidatePath } from "next/cache";
import { redirect } from "next/navigation";
import { supabaseAdmin } from "@/lib/supabase-admin";

async function uploadPhoto(file: File): Promise<string | null> {
  const ext = file.name.split(".").pop() ?? "jpg";
  const filename = `${Date.now()}-${Math.random().toString(36).slice(2, 8)}.${ext}`;
  const buffer = await file.arrayBuffer();
  const { error } = await supabaseAdmin.storage
    .from("dish-photos")
    .upload(filename, buffer, { contentType: file.type, upsert: true });
  if (error) return null;
  const { data } = supabaseAdmin.storage.from("dish-photos").getPublicUrl(filename);
  return data.publicUrl;
}

export async function saveDish(formData: FormData) {
  const id = (formData.get("id") as string) || null;
  const allergensRaw = (formData.get("allergens") as string) || "";

  let photo: string | null = (formData.get("photo_url") as string) || null;
  const photoFile = formData.get("photo") as File | null;
  if (photoFile && photoFile.size > 0) {
    const uploaded = await uploadPhoto(photoFile);
    if (uploaded) photo = uploaded;
  }

  const category_id = formData.get("category_id") as string;

  const payload = {
    name: formData.get("name") as string,
    description: (formData.get("description") as string) || null,
    price: formData.get("price") as string,
    weight: (formData.get("weight") as string) || null,
    category_id,
    badge: (formData.get("badge") as string) || null,
    photo,
    kcal: formData.get("kcal") ? Number(formData.get("kcal")) : null,
    protein: formData.get("protein") ? Number(formData.get("protein")) : null,
    fat: formData.get("fat") ? Number(formData.get("fat")) : null,
    carbs: formData.get("carbs") ? Number(formData.get("carbs")) : null,
    composition: (formData.get("composition") as string) || null,
    allergens: allergensRaw
      ? allergensRaw.split(",").map((s) => s.trim()).filter(Boolean)
      : null,
  };

  if (id) {
    await supabaseAdmin.from("dishes").update(payload).eq("id", id);
  } else {
    // Новое блюдо — в конец своей категории
    const { data: last } = await supabaseAdmin
      .from("dishes")
      .select("sort_order")
      .eq("category_id", category_id)
      .order("sort_order", { ascending: false })
      .limit(1);
    const nextOrder = (last?.[0]?.sort_order ?? 0) + 1;
    await supabaseAdmin.from("dishes").insert({ ...payload, sort_order: nextOrder });
  }

  revalidatePath("/menu");
  revalidatePath("/admin");
  redirect("/admin/dishes");
}

export async function deleteDish(id: string) {
  await supabaseAdmin.from("dishes").delete().eq("id", id);
  revalidatePath("/menu");
  revalidatePath("/admin");
  revalidatePath("/admin/dishes");
}

// Переупорядочивание блюд внутри категории
export async function reorderDishes(orderedIds: string[]) {
  await Promise.all(
    orderedIds.map((id, i) =>
      supabaseAdmin.from("dishes").update({ sort_order: i + 1 }).eq("id", id),
    ),
  );
  revalidatePath("/menu");
}
