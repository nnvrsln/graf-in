import { createClient } from "@supabase/supabase-js";

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.SUPABASE_SERVICE_ROLE_KEY,
  { auth: { persistSession: false } },
);

const categories = [
  { id: "zakuski", title: "Закуски", sort_order: 1 },
  { id: "salaty", title: "Салаты", sort_order: 2 },
  { id: "osnovnye", title: "Основные блюда", sort_order: 3 },
  { id: "deserty", title: "Десерты", sort_order: 4 },
  { id: "napitki", title: "Напитки", sort_order: 5 },
];

const dishes = [
  // ───────── Закуски ─────────
  {
    category_id: "zakuski", sort_order: 1, name: "Тартар из говядины",
    description: "Вырезка, перепелиный желток, каперсы, бриошь",
    price: "890 ₽", weight: "120 г", badge: "hit",
    photo: "photo-1546069901-ba9599a7e63c",
    kcal: 320, protein: 24, fat: 19, carbs: 12,
    composition: "Говяжья вырезка, перепелиный желток, каперсы, дижонская горчица, бриошь, оливковое масло, лук-шалот, зелень.",
    allergens: ["Глютен", "Яйцо", "Горчица"],
  },
  {
    category_id: "zakuski", sort_order: 2, name: "Севиче из сибаса",
    description: "Лайм, чили, кинза, авокадо, лук-шалот",
    price: "920 ₽", weight: "110 г", badge: "hot",
    photo: "photo-1559339352-11d035aa65de",
    kcal: 210, protein: 22, fat: 9, carbs: 7,
    composition: "Филе сибаса, сок лайма, перец чили, кинза, авокадо, лук-шалот, оливковое масло, морская соль.",
    allergens: ["Рыба"],
  },
  {
    category_id: "zakuski", sort_order: 3, name: "Карпаччо из белых грибов",
    description: "Трюфельное масло, пармезан, руккола",
    price: "760 ₽", weight: "90 г", badge: null,
    photo: "photo-1432139555190-58524dae6a55",
    kcal: 180, protein: 7, fat: 14, carbs: 5,
    composition: "Белые грибы, трюфельное масло, пармезан, руккола, сок лимона, чёрный перец.",
    allergens: ["Молоко"],
  },
  {
    category_id: "zakuski", sort_order: 4, name: "Хумус с томлёной свёклой",
    description: "Нут, тахини, печёная свёкла, лепёшка",
    price: "540 ₽", weight: "180 г", badge: "veg",
    photo: "photo-1540189549336-e6e99c3679fe",
    kcal: 290, protein: 9, fat: 14, carbs: 32,
    composition: "Нут, паста тахини, печёная свёкла, чеснок, сок лимона, оливковое масло, зира, пшеничная лепёшка.",
    allergens: ["Глютен", "Кунжут"],
  },

  // ───────── Салаты ─────────
  {
    category_id: "salaty", sort_order: 1, name: "Зелёный салат с авокадо",
    description: "Микс салатов, авокадо, огурец, кунжутная заправка",
    price: "620 ₽", weight: "160 г", badge: "veg",
    photo: "photo-1505253716362-afaea1d3d1af",
    kcal: 240, protein: 5, fat: 18, carbs: 14,
    composition: "Микс салатов, авокадо, огурец, эдамаме, кунжутная заправка, семена кунжута, лайм.",
    allergens: ["Кунжут", "Соя"],
  },
  {
    category_id: "salaty", sort_order: 2, name: "Тёплый салат с уткой",
    description: "Конфи из утки, груша, орех, бальзамик",
    price: "780 ₽", weight: "190 г", badge: "hit",
    photo: "photo-1551248429-40975aa4de74",
    kcal: 360, protein: 19, fat: 24, carbs: 16,
    composition: "Конфи из утиной ножки, груша, грецкий орех, микс салатов, бальзамический соус, оливковое масло.",
    allergens: ["Орехи"],
  },
  {
    category_id: "salaty", sort_order: 3, name: "Боул с булгуром и томатами",
    description: "Булгур, спаржа, черри, тыквенные семечки",
    price: "590 ₽", weight: "220 г", badge: "veg",
    photo: "photo-1547592180-85f173990554",
    kcal: 310, protein: 11, fat: 12, carbs: 40,
    composition: "Булгур, зелёная спаржа, томаты черри, тыквенные семечки, оливковое масло, лимон, петрушка.",
    allergens: ["Глютен"],
  },
  {
    category_id: "salaty", sort_order: 4, name: "Салат шефа с авокадо",
    description: "Зелень, авокадо, цитрус, съедобные цветы",
    price: "690 ₽", weight: "180 г", badge: "veg",
    photo: "photo-1484980972926-edee96e0960d",
    kcal: 260, protein: 6, fat: 19, carbs: 15,
    composition: "Микс салатов, авокадо, апельсин, гранат, нут, съедобные цветы, заправка из оливкового масла и цитруса.",
    allergens: [],
  },

  // ───────── Основные блюда ─────────
  {
    category_id: "osnovnye", sort_order: 1, name: "Утиная грудка",
    description: "Вишнёвый соус, корень сельдерея, тимьян",
    price: "1 290 ₽", weight: "220 г", badge: "hit",
    photo: "photo-1467003909585-2f8a72700288",
    kcal: 520, protein: 34, fat: 32, carbs: 18,
    composition: "Утиная грудка, вишнёвый соус, пюре из корня сельдерея, тимьян, сливочное масло, демигляс.",
    allergens: ["Молоко"],
  },
  {
    category_id: "osnovnye", sort_order: 2, name: "Чёрная треска мисо",
    description: "Маринад мисо 48 ч, понзу, зелёный лук",
    price: "1 540 ₽", weight: "180 г", badge: null,
    photo: "photo-1414235077428-338989a2e8c0",
    kcal: 410, protein: 30, fat: 26, carbs: 9,
    composition: "Чёрная треска, паста мисо, мирин, саке, соус понзу, зелёный лук, кунжут.",
    allergens: ["Рыба", "Соя", "Кунжут"],
  },
  {
    category_id: "osnovnye", sort_order: 3, name: "Рибай с винным соусом",
    description: "Мраморная говядина, соус из красного вина, овощи гриль",
    price: "1 690 ₽", weight: "250 г", badge: "hit",
    photo: "photo-1432139509613-5c4255815697",
    kcal: 620, protein: 42, fat: 44, carbs: 10,
    composition: "Стейк рибай, соус из красного вина, брокколини, томлёная капуста, картофель конфи, тимьян, сливочное масло.",
    allergens: ["Молоко", "Сульфиты"],
  },
  {
    category_id: "osnovnye", sort_order: 4, name: "Рёбра ягнёнка",
    description: "Медленное томление, соус барбекю, овощи",
    price: "1 480 ₽", weight: "320 г", badge: "hot",
    photo: "photo-1544025162-d76694265947",
    kcal: 680, protein: 38, fat: 48, carbs: 22,
    composition: "Рёбра ягнёнка, соус барбекю, картофель фри, томаты, оливки, чеснок, копчёная паприка.",
    allergens: [],
  },
  {
    category_id: "osnovnye", sort_order: 5, name: "Курица томлёная с томатами",
    description: "Бедро курицы, томаты, базилик, чиабатта",
    price: "920 ₽", weight: "280 г", badge: null,
    photo: "photo-1604908176997-125f25cc6f3d",
    kcal: 470, protein: 33, fat: 24, carbs: 28,
    composition: "Куриное бедро, томаты, болгарский перец, базилик, чеснок, оливковое масло, чиабатта.",
    allergens: ["Глютен"],
  },
  {
    category_id: "osnovnye", sort_order: 6, name: "Ризотто с лесными грибами",
    description: "Карнароли, белые грибы, трюфель",
    price: "980 ₽", weight: "240 г", badge: "veg",
    photo: "photo-1476224203421-9ac39bcb3327",
    kcal: 480, protein: 12, fat: 22, carbs: 56,
    composition: "Рис карнароли, белые грибы, шампиньоны, трюфельное масло, пармезан, сливочное масло, овощной бульон, лук.",
    allergens: ["Молоко"],
  },
  {
    category_id: "osnovnye", sort_order: 7, name: "Паста с песто",
    description: "Фарфалле, соус песто, черри, руккола",
    price: "760 ₽", weight: "220 г", badge: "veg",
    photo: "photo-1473093295043-cdd812d0e601",
    kcal: 520, protein: 14, fat: 24, carbs: 60,
    composition: "Паста фарфалле, соус песто, томаты черри, руккола, пармезан, кедровый орех, оливковое масло.",
    allergens: ["Глютен", "Молоко", "Орехи"],
  },

  // ───────── Десерты ─────────
  {
    category_id: "deserty", sort_order: 1, name: "Тарт с грушей",
    description: "Песочное тесто, карамель, ваниль",
    price: "520 ₽", weight: "140 г", badge: null,
    photo: "photo-1488477181946-6428a0291777",
    kcal: 430, protein: 6, fat: 21, carbs: 54,
    composition: "Песочное тесто, груша, солёная карамель, ванильный крем, сливочное масло, миндальная мука.",
    allergens: ["Глютен", "Молоко", "Яйцо", "Орехи"],
  },
  {
    category_id: "deserty", sort_order: 2, name: "Шоколадный фондан",
    description: "Тёплый шоколад, мороженое, малина",
    price: "560 ₽", weight: "150 г", badge: "hit",
    photo: "photo-1606313564200-e75d5e30476c",
    kcal: 540, protein: 8, fat: 30, carbs: 58,
    composition: "Тёмный шоколад 70%, сливочное масло, яйцо, сахар, мука, ванильное мороженое, свежая малина.",
    allergens: ["Глютен", "Молоко", "Яйцо"],
  },
  {
    category_id: "deserty", sort_order: 3, name: "Малиновый бисквит",
    description: "Ваниль, маскарпоне, свежая малина",
    price: "540 ₽", weight: "160 г", badge: null,
    photo: "photo-1565958011703-44f9829ba187",
    kcal: 460, protein: 7, fat: 24, carbs: 52,
    composition: "Ванильный бисквит, крем из маскарпоне, свежая малина, сахарная пудра, сливки.",
    allergens: ["Глютен", "Молоко", "Яйцо"],
  },
  {
    category_id: "deserty", sort_order: 4, name: "Шоколадное парфе",
    description: "Мороженое, шоколадное печенье, сливки",
    price: "490 ₽", weight: "180 г", badge: null,
    photo: "photo-1563805042-7684c019e1cb",
    kcal: 510, protein: 8, fat: 28, carbs: 56,
    composition: "Шоколадное мороженое, шоколадное печенье, взбитые сливки, шоколадный соус, какао.",
    allergens: ["Глютен", "Молоко", "Яйцо", "Соя"],
  },
  {
    category_id: "deserty", sort_order: 5, name: "Фруктовые тарталетки",
    description: "Песочная корзинка, крем, сезонные фрукты",
    price: "580 ₽", weight: "150 г", badge: "veg",
    photo: "photo-1495147466023-ac5c588e2e94",
    kcal: 390, protein: 5, fat: 16, carbs: 58,
    composition: "Песочное тесто, заварной крем, клубника, киви, виноград, персик, абрикосовая глазурь.",
    allergens: ["Глютен", "Молоко", "Яйцо"],
  },

  // ───────── Напитки ─────────
  {
    category_id: "napitki", sort_order: 1, name: "Авторский коктейль",
    description: "Виски, биттер, розмарин, цитрус",
    price: "690 ₽", weight: "200 мл", badge: "hit",
    photo: "photo-1514362545857-3bc16c4c7d1b",
    kcal: 220, protein: 0, fat: 0, carbs: 18,
    composition: "Виски, ароматический биттер, сахарный сироп, розмарин, цедра апельсина, лёд.",
    allergens: [],
  },
  {
    category_id: "napitki", sort_order: 2, name: "Мохито",
    description: "Белый ром, мята, лайм, содовая",
    price: "590 ₽", weight: "350 мл", badge: null,
    photo: "photo-1551538827-9c037cb4f32a",
    kcal: 180, protein: 0, fat: 0, carbs: 22,
    composition: "Белый ром, свежая мята, лайм, тростниковый сахар, содовая, лёд.",
    allergens: [],
  },
  {
    category_id: "napitki", sort_order: 3, name: "Бокал вина",
    description: "Красное / белое сухое на выбор",
    price: "650 ₽", weight: "150 мл", badge: null,
    photo: "photo-1437418747212-8d9709afab22",
    kcal: 120, protein: 0, fat: 0, carbs: 4,
    composition: "Вино сухое (красное или белое) из винной карты ресторана.",
    allergens: ["Сульфиты"],
  },
  {
    category_id: "napitki", sort_order: 4, name: "Холодный кофе",
    description: "Эспрессо, молоко, лёд",
    price: "390 ₽", weight: "300 мл", badge: null,
    photo: "photo-1461023058943-07fcbe16d735",
    kcal: 140, protein: 4, fat: 5, carbs: 18,
    composition: "Двойной эспрессо, молоко, сахарный сироп, лёд.",
    allergens: ["Молоко"],
  },
  {
    category_id: "napitki", sort_order: 5, name: "Капучино",
    description: "Эспрессо и бархатное молоко",
    price: "320 ₽", weight: "200 мл", badge: null,
    photo: "photo-1510972527921-ce03766a1cf1",
    kcal: 110, protein: 6, fat: 6, carbs: 9,
    composition: "Эспрессо, вспененное молоко.",
    allergens: ["Молоко"],
  },
  {
    category_id: "napitki", sort_order: 6, name: "Домашний лимонад",
    description: "Сезонный, со льдом и лаймом",
    price: "350 ₽", weight: "400 мл", badge: "veg",
    photo: "photo-1556679343-c7306c1976bc",
    kcal: 130, protein: 0, fat: 0, carbs: 32,
    composition: "Сезонные ягоды и фрукты, лайм, тростниковый сахар, газированная вода, лёд, мята.",
    allergens: [],
  },
];

async function main() {
  console.log("Очистка таблиц…");
  await supabase.from("dishes").delete().neq("id", "00000000-0000-0000-0000-000000000000");
  await supabase.from("categories").delete().neq("id", "___never___");

  console.log("Вставка категорий…");
  const { error: catErr } = await supabase.from("categories").insert(categories);
  if (catErr) {
    console.error("Ошибка категорий:", catErr);
    process.exit(1);
  }

  console.log(`Вставка блюд (${dishes.length})…`);
  const { error: dishErr } = await supabase.from("dishes").insert(dishes);
  if (dishErr) {
    console.error("Ошибка блюд:", dishErr);
    process.exit(1);
  }

  const { count: cCount } = await supabase
    .from("categories")
    .select("*", { count: "exact", head: true });
  const { count: dCount } = await supabase
    .from("dishes")
    .select("*", { count: "exact", head: true });

  console.log(`Готово: категорий ${cCount}, блюд ${dCount}.`);
}

main();
