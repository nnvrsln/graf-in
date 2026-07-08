import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  experimental: {
    // Фото приходят в server action внутри тела запроса. Дефолт Next — 1 MB,
    // чего мало даже для сжатого фото. Поднимаем до 4 MB (потолок Vercel — 4.5 MB).
    // Основную нагрузку снимает сжатие фото на клиенте (см. DishForm).
    serverActions: {
      bodySizeLimit: "4mb",
    },
  },
  images: {
    remotePatterns: [
      { protocol: "https", hostname: "images.unsplash.com" },
      { protocol: "https", hostname: "eivblmjppwaqkawiwzhl.supabase.co" },
    ],
  },
};

export default nextConfig;
