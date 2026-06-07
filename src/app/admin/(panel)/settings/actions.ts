"use server";

import { revalidatePath } from "next/cache";
import { supabaseAdmin } from "@/lib/supabase-admin";

export async function saveSettings(formData: FormData) {
  const textKeys = [
    "restaurant_name",
    "tagline",
    "chef_name",
    "chef_title",
  ];

  let chefPhoto = formData.get("chef_photo") as string;
  const photoFile = formData.get("chef_photo_file") as File | null;
  if (photoFile && photoFile.size > 0) {
    const ext = photoFile.name.split(".").pop() ?? "jpg";
    const filename = `chef-${Date.now()}.${ext}`;
    const buffer = await photoFile.arrayBuffer();
    const { error } = await supabaseAdmin.storage
      .from("dish-photos")
      .upload(filename, buffer, { contentType: photoFile.type, upsert: true });
    if (!error) {
      const { data } = supabaseAdmin.storage
        .from("dish-photos")
        .getPublicUrl(filename);
      chefPhoto = data.publicUrl;
    }
  }

  const upserts = [
    ...textKeys.map((key) => ({ key, value: formData.get(key) as string })),
    { key: "chef_photo", value: chefPhoto },
  ];

  await supabaseAdmin
    .from("restaurant_settings")
    .upsert(upserts, { onConflict: "key" });

  revalidatePath("/");
  revalidatePath("/menu");
  return { ok: true };
}
