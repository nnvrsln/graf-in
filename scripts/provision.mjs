import { createClient } from "@supabase/supabase-js";
const s = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.SUPABASE_SERVICE_ROLE_KEY,
  { auth: { persistSession: false } },
);

// 1. Storage bucket для фото (публичное чтение)
const { error: bErr } = await s.storage.createBucket("dish-photos", {
  public: true,
  fileSizeLimit: "5MB",
});
console.log("bucket:", bErr ? `ERR ${bErr.message}` : "создан dish-photos (public)");

// 2. Проверка таблицы настроек
const { error: tErr } = await s.from("restaurant_settings").select("key").limit(1);
console.log("settings table:", tErr ? `ОТСУТСТВУЕТ (${tErr.message})` : "есть");
