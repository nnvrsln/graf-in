import { supabase } from "./supabase";

export type SiteSettings = {
  restaurant_name: string;
  tagline: string;
  chef_name: string;
  chef_title: string;
  chef_photo: string;
};

// Дефолты — используются, если таблицы ещё нет или значение пустое
export const defaultSettings: SiteSettings = {
  restaurant_name: "Графин",
  tagline:
    "Место для тёплых встреч, вкусных ужинов и атмосферы, в которую хочется возвращаться снова",
  chef_name: "Расул Магомедов",
  chef_title: "Главный шеф · идейный вдохновитель",
  chef_photo:
    "https://images.unsplash.com/photo-1577219491135-ce391730fb2c?auto=format&fit=crop&w=120&h=120&q=70",
};

export async function getSettings(): Promise<SiteSettings> {
  const { data, error } = await supabase
    .from("restaurant_settings")
    .select("key, value");

  if (error || !data) return defaultSettings;

  const map = Object.fromEntries(
    data.filter((r) => r.value).map((r) => [r.key, r.value as string]),
  );
  return { ...defaultSettings, ...map };
}
