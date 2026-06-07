-- ============================================================
-- ГРАФИН — настройки ресторана + Storage bucket
-- Запусти в Supabase: SQL Editor → New query → Run
-- ============================================================

-- Настройки ресторана (ключ-значение)
create table if not exists restaurant_settings (
  key   text primary key,
  value text
);

alter table restaurant_settings enable row level security;
create policy "public read settings" on restaurant_settings for select using (true);

insert into restaurant_settings (key, value) values
  ('restaurant_name',  'Графин'),
  ('tagline',          'Место для тёплых встреч, вкусных ужинов и атмосферы, в которую хочется возвращаться снова'),
  ('chef_name',        'Расул Магомедов'),
  ('chef_title',       'Главный шеф · идейный вдохновитель'),
  ('chef_photo',       'https://images.unsplash.com/photo-1577219491135-ce391730fb2c?auto=format&fit=crop&w=120&h=120&q=70')
on conflict (key) do nothing;

-- Storage bucket для фото блюд (публичное чтение)
insert into storage.buckets (id, name, public)
values ('dish-photos', 'dish-photos', true)
on conflict (id) do nothing;

create policy "public read dish photos"
  on storage.objects for select
  using (bucket_id = 'dish-photos');
