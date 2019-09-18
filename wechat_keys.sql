/*
 Navicat Premium Data Transfer

 Source Server         : Pgsql
 Source Server Type    : PostgreSQL
 Source Server Version : 90410
 Source Host           : rm-2zewr0i57381z12rg.pg.rds.aliyuncs.com:3433
 Source Catalog        : program
 Source Schema         : public

 Target Server Type    : PostgreSQL
 Target Server Version : 90410
 File Encoding         : 65001

 Date: 18/09/2019 17:55:49
*/


-- ----------------------------
-- Table structure for wechat_keys
-- ----------------------------
DROP TABLE IF EXISTS "public"."wechat_keys";
CREATE TABLE "public"."wechat_keys" (
  "id" int8 NOT NULL DEFAULT nextval('wechat_keys_id_seq'::regclass),
  "appid" varchar(64) COLLATE "pg_catalog"."default" NOT NULL,
  "msgtype" varchar(64) COLLATE "pg_catalog"."default" NOT NULL,
  "keys" varchar(32) COLLATE "pg_catalog"."default" NOT NULL,
  "content" json NOT NULL,
  "sort" int8 NOT NULL DEFAULT 0,
  "status" int2 NOT NULL DEFAULT 1,
  "create_by" int8 NOT NULL DEFAULT 0,
  "msg_id" varchar(64) COLLATE "pg_catalog"."default" NOT NULL,
  "create_at" timestamp(6) NOT NULL DEFAULT now(),
  "update_at" timestamp(6)
)
WITH (fillfactor=100)
;
COMMENT ON COLUMN "public"."wechat_keys"."appid" IS '公众号APPID';
COMMENT ON COLUMN "public"."wechat_keys"."msgtype" IS '类型，text 文件消息，image 图片消息，news 图文消息';
COMMENT ON COLUMN "public"."wechat_keys"."keys" IS '关键字';
COMMENT ON COLUMN "public"."wechat_keys"."content" IS '消息体';
COMMENT ON COLUMN "public"."wechat_keys"."sort" IS '排序字段';
COMMENT ON COLUMN "public"."wechat_keys"."status" IS '0 禁用，1 启用';
COMMENT ON COLUMN "public"."wechat_keys"."create_by" IS '创建人';
COMMENT ON COLUMN "public"."wechat_keys"."msg_id" IS '消息体id';
COMMENT ON COLUMN "public"."wechat_keys"."create_at" IS '创建时间';
COMMENT ON COLUMN "public"."wechat_keys"."update_at" IS '修改时间';
COMMENT ON TABLE "public"."wechat_keys" IS '微信关键字';

-- ----------------------------
-- Records of wechat_keys
-- ----------------------------
INSERT INTO "public"."wechat_keys" VALUES (3, 'wx3fb38d0d15ae7820', 'text', 'subscribe', '{
"content":"您好！欢迎使用 overtrue。"
}

', 0, 1, 0, 'msgb7ad622270cc0', '2019-08-29 14:14:43', '2019-08-29 14:14:53');
INSERT INTO "public"."wechat_keys" VALUES (6, 'wx3fb38d0d15ae7820', 'text', 'V1001_TODAY_MUSIC', '{
"content":"您好！欢迎点击菜单 V1001_TODAY_MUSIC"
}', 0, 1, 0, 'msg957e8c9c5d6df', '2019-08-30 11:05:15', NULL);
INSERT INTO "public"."wechat_keys" VALUES (7, 'wx3fb38d0d15ae7820', 'msgmenu', 'menu', ' {
    "head_content": "您对本次服务是否满意呢? ",
    "list": [
      {
        "id": "101",
        "content": "满意"
      },
      {
        "id": "102",
        "content": "不满意"
      }
    ],
    "tail_content": "欢迎再次光临"
  }', 0, 1, 0, 'msgc19495636a4b6', '2019-08-30 14:27:26.038518', NULL);
INSERT INTO "public"."wechat_keys" VALUES (5, 'wx3fb38d0d15ae7820', 'text', 'foo', '{
"content":"您好！欢迎使用 foo二维码"
}', 0, 1, 0, 'msge5e4f1076c6cc', '2019-08-30 11:05:15.283044', NULL);
INSERT INTO "public"."wechat_keys" VALUES (8, 'wx3fb38d0d15ae7820', 'text', 'boo', '{
"content":"您好！欢迎使用 boo二维码!!!"
}', 0, 1, 0, 'msg55bab19bb4cf8', '2019-09-05 11:05:15', NULL);

-- ----------------------------
-- Primary Key structure for table wechat_keys
-- ----------------------------
ALTER TABLE "public"."wechat_keys" ADD CONSTRAINT "pk_public_wechat_keys" PRIMARY KEY ("id");
