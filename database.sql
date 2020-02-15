CREATE DATABASE prueba;
USE prueba;

CREATE TABLE users(
    id      int(11) auto_increment not null,
    documento   int(20) not null,
    nombre      varchar(255) not null,
    email       varchar(255) not null,
    celular     int(20) not null,
    created_at  datetime,
    updated_at  datetime,
    remember_token  varchar(255),
    CONSTRAINT pk_users PRIMARY KEY(id),
    CONSTRAINT uq_documento UNIQUE(documento),
    CONSTRAINT uq_email UNIQUE(email),
    CONSTRAINT uq_celular UNIQUE(celular)
)

INSERT INTO users(null, '1036400564', 'sergio', 'sergioriosp04@gmail.com', '3193613743', CURDATE(), CURDATE(), null);

CREATE TABLE billetera(
    id      int(11) auto_increment not null,
    user_id int(11),
    saldo decimal(10, 2) not null,
    created_at  datetime,
    updated_at  datetime,
    CONSTRAINT pk_billetera PRIMARY KEY(id),
    CONSTRAINT fk_billetera_users FOREIGN KEY(user_id) REFERENCES users(id)
)

INSERT INTO users(null, null

CREATE TABLE tokens(
    id      int(11) auto_increment not null,
    user_id int(11) not null,
    token   varchar(20) not null,
    created_at  datetime,
    updated_at  datetime,
    CONSTRAINT pk_tokens PRIMARY KEY(id)
)
