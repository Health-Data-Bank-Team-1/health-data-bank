--
-- PostgreSQL database dump
--

\restrict TaxKynQd6bQWm2KoTewMIUdTEb3UoEgC1SseHVBJ9Qs5QGkuqXqdP0aJrZ3JDtW

-- Dumped from database version 18.1
-- Dumped by pg_dump version 18.1

-- Started on 2026-02-02 16:11:16

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 2 (class 3079 OID 16516)
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- TOC entry 5207 (class 0 OID 0)
-- Dependencies: 2
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


--
-- TOC entry 883 (class 1247 OID 16528)
-- Name: account_type_enum; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.account_type_enum AS ENUM (
    'User',
    'Researcher',
    'HealthcareProvider',
    'Admin'
);


ALTER TYPE public.account_type_enum OWNER TO postgres;

--
-- TOC entry 898 (class 1247 OID 16570)
-- Name: field_type_enum; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.field_type_enum AS ENUM (
    'Text',
    'Number',
    'Date',
    'Boolean',
    'Dropdown'
);


ALTER TYPE public.field_type_enum OWNER TO postgres;

--
-- TOC entry 895 (class 1247 OID 16562)
-- Name: form_status_enum; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.form_status_enum AS ENUM (
    'DRAFT',
    'PUBLISHED',
    'ARCHIVED'
);


ALTER TYPE public.form_status_enum OWNER TO postgres;

--
-- TOC entry 904 (class 1247 OID 16590)
-- Name: goal_status_enum; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.goal_status_enum AS ENUM (
    'ACTIVE',
    'MET',
    'EXPIRED'
);


ALTER TYPE public.goal_status_enum OWNER TO postgres;

--
-- TOC entry 889 (class 1247 OID 16544)
-- Name: method_type_enum; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.method_type_enum AS ENUM (
    'SMS',
    'TOTP',
    'Email'
);


ALTER TYPE public.method_type_enum OWNER TO postgres;

--
-- TOC entry 907 (class 1247 OID 16598)
-- Name: report_type_enum; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.report_type_enum AS ENUM (
    'Aggregated',
    'Comparative'
);


ALTER TYPE public.report_type_enum OWNER TO postgres;

--
-- TOC entry 892 (class 1247 OID 16552)
-- Name: role_name_enum; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.role_name_enum AS ENUM (
    'User',
    'Researcher',
    'Admin',
    'Provider'
);


ALTER TYPE public.role_name_enum OWNER TO postgres;

--
-- TOC entry 886 (class 1247 OID 16538)
-- Name: status_enum; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.status_enum AS ENUM (
    'ACTIVE',
    'DEACTIVATED'
);


ALTER TYPE public.status_enum OWNER TO postgres;

--
-- TOC entry 901 (class 1247 OID 16582)
-- Name: submission_status_enum; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.submission_status_enum AS ENUM (
    'SUBMITTED',
    'FLAGGED',
    'APPROVED'
);


ALTER TYPE public.submission_status_enum OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 220 (class 1259 OID 16388)
-- Name: account; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.account (
);


ALTER TABLE public.account OWNER TO postgres;

--
-- TOC entry 5208 (class 0 OID 0)
-- Dependencies: 220
-- Name: TABLE account; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.account IS 'Central table for user accounts';


--
-- TOC entry 227 (class 1259 OID 16684)
-- Name: account_roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.account_roles (
    account_id uuid NOT NULL,
    role_id uuid NOT NULL
);


ALTER TABLE public.account_roles OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 16603)
-- Name: accounts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.accounts (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    account_type public.account_type_enum NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    status public.status_enum DEFAULT 'ACTIVE'::public.status_enum,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.accounts OWNER TO postgres;

--
-- TOC entry 235 (class 1259 OID 16810)
-- Name: aggregated_data; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.aggregated_data (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    report_id uuid,
    metrics jsonb,
    anonymization_level integer DEFAULT 1
);


ALTER TABLE public.aggregated_data OWNER TO postgres;

--
-- TOC entry 237 (class 1259 OID 16826)
-- Name: audit_logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.audit_logs (
    id bigint NOT NULL,
    actor_id uuid,
    action_type character varying(255) NOT NULL,
    "timestamp" timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.audit_logs OWNER TO postgres;

--
-- TOC entry 236 (class 1259 OID 16825)
-- Name: audit_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.audit_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.audit_logs_id_seq OWNER TO postgres;

--
-- TOC entry 5209 (class 0 OID 0)
-- Dependencies: 236
-- Name: audit_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.audit_logs_id_seq OWNED BY public.audit_logs.id;


--
-- TOC entry 222 (class 1259 OID 16620)
-- Name: authentication_credentials; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.authentication_credentials (
    account_id uuid NOT NULL,
    password_hash character varying(255) NOT NULL,
    last_login timestamp without time zone
);


ALTER TABLE public.authentication_credentials OWNER TO postgres;

--
-- TOC entry 232 (class 1259 OID 16769)
-- Name: dashboards; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dashboards (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    account_id uuid
);


ALTER TABLE public.dashboards OWNER TO postgres;

--
-- TOC entry 229 (class 1259 OID 16713)
-- Name: form_fields; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.form_fields (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    form_template_id uuid,
    label character varying(255) NOT NULL,
    field_type public.field_type_enum NOT NULL,
    validation_rules jsonb
);


ALTER TABLE public.form_fields OWNER TO postgres;

--
-- TOC entry 230 (class 1259 OID 16729)
-- Name: form_submissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.form_submissions (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    account_id uuid,
    form_template_id uuid,
    status public.submission_status_enum DEFAULT 'SUBMITTED'::public.submission_status_enum,
    submitted_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.form_submissions OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 16701)
-- Name: form_templates; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.form_templates (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    version integer NOT NULL,
    status public.form_status_enum DEFAULT 'DRAFT'::public.form_status_enum,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.form_templates OWNER TO postgres;

--
-- TOC entry 231 (class 1259 OID 16748)
-- Name: health_entries; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.health_entries (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    submission_id uuid,
    account_id uuid,
    "timestamp" timestamp without time zone NOT NULL,
    encrypted_values jsonb NOT NULL
);


ALTER TABLE public.health_entries OWNER TO postgres;

--
-- TOC entry 233 (class 1259 OID 16781)
-- Name: health_goals; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.health_goals (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    account_id uuid,
    target_value double precision NOT NULL,
    start_date date NOT NULL,
    end_date date,
    status public.goal_status_enum DEFAULT 'ACTIVE'::public.goal_status_enum
);


ALTER TABLE public.health_goals OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 16657)
-- Name: permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.permissions (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    permission_name character varying(100) NOT NULL,
    scope character varying(100)
);


ALTER TABLE public.permissions OWNER TO postgres;

--
-- TOC entry 234 (class 1259 OID 16796)
-- Name: reports; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.reports (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    researcher_id uuid,
    report_type public.report_type_enum NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.reports OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 16667)
-- Name: role_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.role_permissions (
    role_id uuid NOT NULL,
    permission_id uuid NOT NULL
);


ALTER TABLE public.role_permissions OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 16647)
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.roles (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    role_name public.role_name_enum NOT NULL
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 16632)
-- Name: two_factor_methods; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.two_factor_methods (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    account_id uuid,
    method_type public.method_type_enum NOT NULL,
    secret_key character varying(255) NOT NULL,
    enabled boolean DEFAULT false
);


ALTER TABLE public.two_factor_methods OWNER TO postgres;

--
-- TOC entry 4981 (class 2604 OID 16829)
-- Name: audit_logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_logs ALTER COLUMN id SET DEFAULT nextval('public.audit_logs_id_seq'::regclass);


--
-- TOC entry 5184 (class 0 OID 16388)
-- Dependencies: 220
-- Data for Name: account; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.account  FROM stdin;
\.


--
-- TOC entry 5191 (class 0 OID 16684)
-- Dependencies: 227
-- Data for Name: account_roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.account_roles (account_id, role_id) FROM stdin;
\.


--
-- TOC entry 5185 (class 0 OID 16603)
-- Dependencies: 221
-- Data for Name: accounts; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.accounts (id, account_type, name, email, status, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5199 (class 0 OID 16810)
-- Dependencies: 235
-- Data for Name: aggregated_data; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.aggregated_data (id, report_id, metrics, anonymization_level) FROM stdin;
\.


--
-- TOC entry 5201 (class 0 OID 16826)
-- Dependencies: 237
-- Data for Name: audit_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.audit_logs (id, actor_id, action_type, "timestamp") FROM stdin;
\.


--
-- TOC entry 5186 (class 0 OID 16620)
-- Dependencies: 222
-- Data for Name: authentication_credentials; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.authentication_credentials (account_id, password_hash, last_login) FROM stdin;
\.


--
-- TOC entry 5196 (class 0 OID 16769)
-- Dependencies: 232
-- Data for Name: dashboards; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.dashboards (id, account_id) FROM stdin;
\.


--
-- TOC entry 5193 (class 0 OID 16713)
-- Dependencies: 229
-- Data for Name: form_fields; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.form_fields (id, form_template_id, label, field_type, validation_rules) FROM stdin;
\.


--
-- TOC entry 5194 (class 0 OID 16729)
-- Dependencies: 230
-- Data for Name: form_submissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.form_submissions (id, account_id, form_template_id, status, submitted_at) FROM stdin;
\.


--
-- TOC entry 5192 (class 0 OID 16701)
-- Dependencies: 228
-- Data for Name: form_templates; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.form_templates (id, version, status, description, created_at) FROM stdin;
\.


--
-- TOC entry 5195 (class 0 OID 16748)
-- Dependencies: 231
-- Data for Name: health_entries; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.health_entries (id, submission_id, account_id, "timestamp", encrypted_values) FROM stdin;
\.


--
-- TOC entry 5197 (class 0 OID 16781)
-- Dependencies: 233
-- Data for Name: health_goals; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.health_goals (id, account_id, target_value, start_date, end_date, status) FROM stdin;
\.


--
-- TOC entry 5189 (class 0 OID 16657)
-- Dependencies: 225
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.permissions (id, permission_name, scope) FROM stdin;
\.


--
-- TOC entry 5198 (class 0 OID 16796)
-- Dependencies: 234
-- Data for Name: reports; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.reports (id, researcher_id, report_type, created_at) FROM stdin;
\.


--
-- TOC entry 5190 (class 0 OID 16667)
-- Dependencies: 226
-- Data for Name: role_permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.role_permissions (role_id, permission_id) FROM stdin;
\.


--
-- TOC entry 5188 (class 0 OID 16647)
-- Dependencies: 224
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.roles (id, role_name) FROM stdin;
\.


--
-- TOC entry 5187 (class 0 OID 16632)
-- Dependencies: 223
-- Data for Name: two_factor_methods; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.two_factor_methods (id, account_id, method_type, secret_key, enabled) FROM stdin;
\.


--
-- TOC entry 5210 (class 0 OID 0)
-- Dependencies: 236
-- Name: audit_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.audit_logs_id_seq', 1, false);


--
-- TOC entry 5002 (class 2606 OID 16690)
-- Name: account_roles account_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.account_roles
    ADD CONSTRAINT account_roles_pkey PRIMARY KEY (account_id, role_id);


--
-- TOC entry 4984 (class 2606 OID 16619)
-- Name: accounts accounts_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounts
    ADD CONSTRAINT accounts_email_key UNIQUE (email);


--
-- TOC entry 4986 (class 2606 OID 16617)
-- Name: accounts accounts_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounts
    ADD CONSTRAINT accounts_pkey PRIMARY KEY (id);


--
-- TOC entry 5018 (class 2606 OID 16819)
-- Name: aggregated_data aggregated_data_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.aggregated_data
    ADD CONSTRAINT aggregated_data_pkey PRIMARY KEY (id);


--
-- TOC entry 5020 (class 2606 OID 16834)
-- Name: audit_logs audit_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_pkey PRIMARY KEY (id);


--
-- TOC entry 4988 (class 2606 OID 16626)
-- Name: authentication_credentials authentication_credentials_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.authentication_credentials
    ADD CONSTRAINT authentication_credentials_pkey PRIMARY KEY (account_id);


--
-- TOC entry 5012 (class 2606 OID 16775)
-- Name: dashboards dashboards_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dashboards
    ADD CONSTRAINT dashboards_pkey PRIMARY KEY (id);


--
-- TOC entry 5006 (class 2606 OID 16723)
-- Name: form_fields form_fields_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.form_fields
    ADD CONSTRAINT form_fields_pkey PRIMARY KEY (id);


--
-- TOC entry 5008 (class 2606 OID 16737)
-- Name: form_submissions form_submissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.form_submissions
    ADD CONSTRAINT form_submissions_pkey PRIMARY KEY (id);


--
-- TOC entry 5004 (class 2606 OID 16712)
-- Name: form_templates form_templates_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.form_templates
    ADD CONSTRAINT form_templates_pkey PRIMARY KEY (id);


--
-- TOC entry 5010 (class 2606 OID 16758)
-- Name: health_entries health_entries_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.health_entries
    ADD CONSTRAINT health_entries_pkey PRIMARY KEY (id);


--
-- TOC entry 5014 (class 2606 OID 16790)
-- Name: health_goals health_goals_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.health_goals
    ADD CONSTRAINT health_goals_pkey PRIMARY KEY (id);


--
-- TOC entry 4996 (class 2606 OID 16666)
-- Name: permissions permissions_permission_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_permission_name_key UNIQUE (permission_name);


--
-- TOC entry 4998 (class 2606 OID 16664)
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- TOC entry 5016 (class 2606 OID 16804)
-- Name: reports reports_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reports
    ADD CONSTRAINT reports_pkey PRIMARY KEY (id);


--
-- TOC entry 5000 (class 2606 OID 16673)
-- Name: role_permissions role_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_pkey PRIMARY KEY (role_id, permission_id);


--
-- TOC entry 4992 (class 2606 OID 16654)
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- TOC entry 4994 (class 2606 OID 16656)
-- Name: roles roles_role_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_role_name_key UNIQUE (role_name);


--
-- TOC entry 4990 (class 2606 OID 16641)
-- Name: two_factor_methods two_factor_methods_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.two_factor_methods
    ADD CONSTRAINT two_factor_methods_pkey PRIMARY KEY (id);


--
-- TOC entry 5025 (class 2606 OID 16691)
-- Name: account_roles account_roles_account_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.account_roles
    ADD CONSTRAINT account_roles_account_id_fkey FOREIGN KEY (account_id) REFERENCES public.accounts(id) ON DELETE CASCADE;


--
-- TOC entry 5026 (class 2606 OID 16696)
-- Name: account_roles account_roles_role_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.account_roles
    ADD CONSTRAINT account_roles_role_id_fkey FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- TOC entry 5035 (class 2606 OID 16820)
-- Name: aggregated_data aggregated_data_report_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.aggregated_data
    ADD CONSTRAINT aggregated_data_report_id_fkey FOREIGN KEY (report_id) REFERENCES public.reports(id) ON DELETE CASCADE;


--
-- TOC entry 5036 (class 2606 OID 16835)
-- Name: audit_logs audit_logs_actor_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_actor_id_fkey FOREIGN KEY (actor_id) REFERENCES public.accounts(id);


--
-- TOC entry 5021 (class 2606 OID 16627)
-- Name: authentication_credentials authentication_credentials_account_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.authentication_credentials
    ADD CONSTRAINT authentication_credentials_account_id_fkey FOREIGN KEY (account_id) REFERENCES public.accounts(id) ON DELETE CASCADE;


--
-- TOC entry 5032 (class 2606 OID 16776)
-- Name: dashboards dashboards_account_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dashboards
    ADD CONSTRAINT dashboards_account_id_fkey FOREIGN KEY (account_id) REFERENCES public.accounts(id) ON DELETE CASCADE;


--
-- TOC entry 5027 (class 2606 OID 16724)
-- Name: form_fields form_fields_form_template_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.form_fields
    ADD CONSTRAINT form_fields_form_template_id_fkey FOREIGN KEY (form_template_id) REFERENCES public.form_templates(id) ON DELETE CASCADE;


--
-- TOC entry 5028 (class 2606 OID 16738)
-- Name: form_submissions form_submissions_account_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.form_submissions
    ADD CONSTRAINT form_submissions_account_id_fkey FOREIGN KEY (account_id) REFERENCES public.accounts(id);


--
-- TOC entry 5029 (class 2606 OID 16743)
-- Name: form_submissions form_submissions_form_template_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.form_submissions
    ADD CONSTRAINT form_submissions_form_template_id_fkey FOREIGN KEY (form_template_id) REFERENCES public.form_templates(id);


--
-- TOC entry 5030 (class 2606 OID 16764)
-- Name: health_entries health_entries_account_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.health_entries
    ADD CONSTRAINT health_entries_account_id_fkey FOREIGN KEY (account_id) REFERENCES public.accounts(id);


--
-- TOC entry 5031 (class 2606 OID 16759)
-- Name: health_entries health_entries_submission_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.health_entries
    ADD CONSTRAINT health_entries_submission_id_fkey FOREIGN KEY (submission_id) REFERENCES public.form_submissions(id);


--
-- TOC entry 5033 (class 2606 OID 16791)
-- Name: health_goals health_goals_account_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.health_goals
    ADD CONSTRAINT health_goals_account_id_fkey FOREIGN KEY (account_id) REFERENCES public.accounts(id) ON DELETE CASCADE;


--
-- TOC entry 5034 (class 2606 OID 16805)
-- Name: reports reports_researcher_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reports
    ADD CONSTRAINT reports_researcher_id_fkey FOREIGN KEY (researcher_id) REFERENCES public.accounts(id);


--
-- TOC entry 5023 (class 2606 OID 16679)
-- Name: role_permissions role_permissions_permission_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_permission_id_fkey FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- TOC entry 5024 (class 2606 OID 16674)
-- Name: role_permissions role_permissions_role_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_role_id_fkey FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- TOC entry 5022 (class 2606 OID 16642)
-- Name: two_factor_methods two_factor_methods_account_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.two_factor_methods
    ADD CONSTRAINT two_factor_methods_account_id_fkey FOREIGN KEY (account_id) REFERENCES public.accounts(id) ON DELETE CASCADE;


-- Completed on 2026-02-02 16:11:16

--
-- PostgreSQL database dump complete
--

\unrestrict TaxKynQd6bQWm2KoTewMIUdTEb3UoEgC1SseHVBJ9Qs5QGkuqXqdP0aJrZ3JDtW

