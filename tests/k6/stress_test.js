import http from 'k6/http';
import { check, sleep } from 'k6';
import { parseHTML } from 'k6/html';

const BASE = 'https://simpati.p3tgai-kemenpu-bbwsbrantas.com';
const LOGIN = `${BASE}/login`;

// Set real test credentials via env vars when you run:
//   k6 run -e EMAIL=you@x.com -e PASS=secret loadtest.js
const EMAIL = __ENV.EMAIL || 'loadtest@example.com';
const PASS  = __ENV.PASS  || 'changeme';

export const options = {
  scenarios: {
    hundred_users: {
      executor: 'ramping-vus',
      startVUs: 0,
      stages: [
        { duration: '30s', target: 100 }, // ramp UP to 100 over 30s
        { duration: '1m',  target: 100 }, // hold 100 for 1 min
        { duration: '15s', target: 0 },   // ramp down
      ],
      gracefulRampDown: '10s',
    },
  },
  thresholds: {
    http_req_duration: ['p(95)<2000'], // 95% of requests under 2s
    http_req_failed:   ['rate<0.05'],  // <5% errors
  },
};

export default function () {
  // 1) GET login page -> grab session cookie + CSRF token
  const getRes = http.get(LOGIN);
  check(getRes, { 'login page 200': (r) => r.status === 200 });

  const doc = parseHTML(getRes.body);
  // CI4 CSRF: field name is csrf_test_name (not Laravel's _token)
  const csrfInput = doc.find('input[name="csrf_test_name"]');
  const csrfName  = csrfInput.attr('name')  || 'csrf_test_name';
  const csrfValue = csrfInput.attr('value') || '';

  // 2) POST credentials + token
  // CI4 login form uses name="login" for the email field
  const payload = {
    login:    EMAIL,
    password: PASS,
  };
  if (csrfValue) payload[csrfName] = csrfValue;

  const postRes = http.post(LOGIN, payload, {
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    redirects: 0, // don't follow redirect, just measure the login response
  });

  const loc = postRes.headers['Location'] || '';
  check(postRes, {
    // 302/303 = redirect (success→dashboard or fail→/login), 200 = inline error, 422 = validation
    'login responded':  (r) => [200, 302, 303, 422].includes(r.status),
    'login succeeded':  () => [302, 303].includes(postRes.status) && !loc.endsWith('/login'),
  });

  sleep(1); // pacing: think-time between iterations
}