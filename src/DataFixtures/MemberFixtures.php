<?php

namespace App\DataFixtures;

use App\Entity\Member;
use App\Entity\MemberStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MemberFixtures extends Fixture implements DependentFixtureInterface
{
    public const UNCLE_BILLY = 'William L. Phillips';

    protected $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function load(ObjectManager $manager)
    {
        $memberStatusMember = $this->getReference(MemberStatusFixtures::MEMBER);
        $memberStatusAlumnus = $this->getReference(MemberStatusFixtures::ALUMNUS);
        $memberStatusExpelled = $this->getReference(MemberStatusFixtures::EXPELLED);
        $tag1901Club = $this->getReference(TagFixtures::TAG_1901_CLUB);

        $member = new Member();
        $member->setExternalIdentifier(md5('1-0001'));
        $member->setLocalIdentifier('1-0001');
        $member->setFirstName('Carter');
        $member->setPreferredName('Carter');
        $member->setMiddleName('Ashton');
        $member->setLastName('Jenkins');
        $member->setPrimaryEmail('cjenkins@example.com');
        $member->setBirthDate(new \DateTime('1882-04-09'));
        $member->setClassYear(1902);
        $member->setMailingAddressLine1('310 South Blvd');
        $member->setMailingAddressLine2('');
        $member->setMailingCity('Richmond');
        $member->setMailingState('VA');
        $member->setMailingPostalCode('23220-5706');
        $member->setMailingCountry('United States');
        $member->setMailingLatitude(37.54964400);
        $member->setMailingLongitude(-77.47736000);
        $member->setPrimaryTelephoneNumber('804-353-1901');
        $member->setStatus($memberStatusMember);
        $member->setJoinDate(new \DateTime('November 1, 1901'));
        $member->setDirectoryNotes("Carter Ashton Jenkens was born in Oxford, North Carolina, on April 9, 1882, and received his early education in New Jersey.\n\nHe graduated from Richmond College in June, 1902, and then taught for two years at Chase City, Virginia, Military Academy and Richmond Preparatory. He received a baccalaureate degree in the ministry at Crozer Seminary in Chester, Pennsylvania, and served for more than 20 years as a pastor in churches in Hampton, Norfolk, and Richmond, finally to become an evangelist and conduct revivals throughout the United States. His gift for inspiring oratory was so outstanding that the famed evangelist \"Billy Sunday\" is reported on one occasion to have exclaimed, \"If only the Almighty had blessed me with the voice of Carter Jenkens!\" His twilight years were spent in Louisville, Kentucky, where he died on July 23, 1952.");
        $member->addTag($tag1901Club);
        $member->setPhotoUrl('https://sigep.org/wp-content/uploads/2016/11/jenkins.jpg');
        $manager->persist($member);

        $member = new Member();
        $member->setExternalIdentifier(md5('1-0002'));
        $member->setLocalIdentifier('1-0002');
        $member->setFirstName('Benjamin');
        $member->setPreferredName('Ben');
        $member->setMiddleName('Donald');
        $member->setLastName('Gaw');
        $member->setPrimaryEmail('bgaw@example.com');
        $member->setBirthDate(new \DateTime('1870-08-20'));
        $member->setClassYear(1906);
        $member->setStatus($memberStatusMember);
        $member->setJoinDate(new \DateTime('November 1, 1901'));
        $member->setDirectoryNotes("Benjamin Donald Gaw came to Richmond College, where he worked his way through school, acting as pastor of the East End Baptist Church of Richmond, to graduate in 1906.\n\nHe had come from Stuart's Draft, Virginia, where he was born on August 20, 1870. He married and later received the bachelor of divinity degree at Colgate. For six years thereafter, he was pastor at the West Washington Baptist Church, Washington, D.C., and in 1917 was called to the First Baptist Church in Durham, North Carolina. He died in Washington, D.C. on January 10, 1919, from pneumonia. He is buried in Montgomery, Maryland.");
        $manager->persist($member);

        $member = new Member();
        $member->setExternalIdentifier(md5('1-0003'));
        $member->setLocalIdentifier('1-0003');
        $member->setFirstName('William');
        $member->setPreferredName('Will');
        $member->setMiddleName('Hugh');
        $member->setLastName('Carter');
        $member->setPrimaryEmail('wcarter@example.com');
        $member->setBirthDate(new \DateTime('1878-02-02'));
        $member->setClassYear(1902);
        $member->setStatus($memberStatusMember);
        $member->setJoinDate(new \DateTime('November 1, 1901'));
        $member->setDirectoryNotes("William Hugh Carter was born near Danville in Pittsylvania County, Virginia, February 2, 1878.\n\nHis family moved to Salem, where he attended the public schools. For one year, he taught in a public school in Roanoke County, Virginia, and in September 1897, entered Richmond College to prepare for the Baptist ministry. After being out of college for one year, he received his B.A. degree from Richmond College in June of 1902.\n\nFounder Carter's campus activities included debate, YMCA, and varsity basketball. He became a teacher at Southside Academy in Chase City, Virginia in 1902-1903 and was principal of the Chase City Grade School for the next two years. During this three-year period, he served as the editor of the Chase City Progress. In September, 1905, he entered Crozer Theological Seminary, where Jenkens had gone, and received the bachelor of divinity degree in May, 1908. He then became pastor of the First Baptist Church, Winchester, Virginia, for six-and-a-half years. Subsequent pastorates were at Hertford, North Carolina, three-and-a-half years; Crewe, Virginia, ten-and-a-half years; and Marion Virginia, 18 years.\n\nRetiring from active pastorates, he served as field worker for the Sunday School Department of the Varina Baptist Board of Missions and Education. Brother Carter died in Salem, Virginia on January 5, 1971 at the age of 92.");
        $member->addTag($tag1901Club);
        $manager->persist($member);

        $member = new Member();
        $member->setExternalIdentifier(md5('1-0004'));
        $member->setLocalIdentifier('1-0004');
        $member->setFirstName('William');
        $member->setPreferredName('Bill');
        $member->setMiddleName('Andrew');
        $member->setLastName('Wallace');
        $member->setBirthDate(new \DateTime('1882-05-07'));
        $member->setClassYear(null);
        $member->setStatus($memberStatusMember);
        $member->setJoinDate(new \DateTime('November 1, 1901'));
        $member->setDirectoryNotes("William Andrew Wallace, the second of the roommates at Ryland Hall, was invited to join that group by Gaw, his roommate.\n\nHe came from Gaw's hometown, Stuart's Draft, where he was born on May 7, 1882. He did not graduate but transferred to the Medical College of Virginia for his M.D., on which campus he launched the dormant Virginia Beta Chapter (now Virginia Commonwealth University), becoming its first member. By this act, Sigma Phi Epsilon's expansion began.\n\nHe left the Medical College for an internship in the Boston Floating Hospital, which he left for another internship in a hospital in Richmond. Later, in 1908, he located in Spartanburg, South Carolina, continuing in practice to become one of the best-known medical practitioners in the state, and a devoted SigEp until his death in 1929.");
        $manager->persist($member);

        $member = new Member();
        $member->setExternalIdentifier(md5('1-0005'));
        $member->setLocalIdentifier('1-0005');
        $member->setFirstName('Thomas');
        $member->setPreferredName('Those');
        $member->setMiddleName('Temple');
        $member->setLastName('Wright');
        $member->setPrimaryEmail('twright@example.com');
        $member->setBirthDate(new \DateTime('1883-05-21'));
        $member->setClassYear(1904);
        $member->setStatus($memberStatusMember);
        $member->setJoinDate(new \DateTime('November 1, 1901'));
        $member->setDirectoryNotes("Thomas Temple Wright was born at Locust Grove, Caroline County, Virginia, May 21, 1883.\n\nHe was tutored at home, entered Richmond College in 1900, received the B.A. in 1904, and was graduated from the Engineering College at Cornell University, in 1907.\n\nWright roomed with Jenkens at the \"Cottage.\" His intimate friends knew him as \"Those,\" after the abbreviated form of his name. The fifth member of the Fraternity, he was one of the two founders who returned to college in September, 1902, and as treasurer of the group, signed the corporate charter secured from the Commonwealth of Virginia on October 22, 1902.\n\nWright started his professional career as a United States surveyor with the Mississippi River Commission in Vicksburg, Mississippi. He later became a railroad civil engineer, first with the Canadian Pacific Railroad in Ottawa, Canada, then with the Canadian Northern Railroad on construction in Ontario, and finally with the Baltimore and Ohio. In 1917, \"Those,\" on leave from the B & O, was construction engineer for the United States Army Camp Taylor at Louisville, Kentucky. The following year, he became head of the Warsaw and Fredericksburg offices of the Henrico Lumber Company, making his home in Warsaw, Virginia. In 1933, he and his brothers formed Wright Brothers, Inc., with offices in Richmond, West Point, Tappahannock, and Philadelphia. He continued to be active with this firm for many years. He died on February 15, 1958.");
        $member->setIsLost(true);
        $manager->persist($member);

        $member = new Member();
        $member->setExternalIdentifier(md5('1-0006'));
        $member->setLocalIdentifier('1-0006');
        $member->setFirstName('William');
        $member->setPreferredName('Billy');
        $member->setMiddleName('Lazell');
        $member->setLastName('Phillips');
        $member->setPrimaryEmail('unclebilly@example.org');
        $member->setBirthDate(null);
        $member->setClassYear(null);
        $member->setMailingAddressLine1('2 Ryland Circle');
        $member->setMailingCity('Richmond');
        $member->setMailingState('VA');
        $member->setMailingPostalCode('23226');
        $member->setMailingLatitude(37.5775);
        $member->setMailingLongitude(-77.537222);
        $member->setMailingCountry('United States');
        $member->setStatus($memberStatusMember);
        $member->setJoinDate(new \DateTime('November 1, 1901'));
        $member->setDirectoryNotes("William Lazell Phillips devoted virtually all the mature years of his life to Sigma Phi Epsilon.\n\nA study of the leadership pattern of the founding group reveals that he is the one titan after Jenkens. The latter said to his brothers, \"This is how we must build our Fraternity.\" Phillips built it. Born in Normal, Illinois, in 1873, William L. Phillips came to Richmond College in September, 1901, to study law and the Bible. He attended one year, dropped out a year, and then returned. His pursuit of legal studies gave way to his devotion to Sigma Phi Epsilon and he never graduated.\n\nThe first Conclave at Richmond College in December, 1903, authorized the establishment of the Journal and appointed Uncle Billy as its first editor. The first issue, March, 1904, \"Published by the Grand Council in the interest of the Fraternity,\" reveals that Uncle Billy was determined to make the Journal carry news from all chapters and thus add dignity and strength to his young Fraternity.\n\nIn addition to his work as the first Journal editor, he played some baseball and football (not on the college team), attended the Philogian Literary Society, and attended classes in law. He was the first secretary of Virginia Alpha in 1901-1902.\n\nA complete record of his professional career tells the story of his work for Sigma Phi Epsilon: Editor of the Journal, 1904-1912, 1919-1921; business manager of the Journal, 1904-1911, 1919-1942; member, Ritual Committee, 1907; editor of membership directories, 1915 and 1921; trustee of the Endowment Fund 1925-1939, 1944-1949; trustee of the national Headquarters, 1927-1942; trustee of the Student Loan Fund, 1930-1940; Grand Secretary, 1908-1942; Grand Secretary Emeritus, 1942-1956; Grand Vice President, 1943; Grand President, 1944; National Interfraternity Conference, founder, 1909; Vice Chairman, 1929-1930; member, War Committee, 1942; a founder and Chairman of College Fraternity Secretaries Association 1939-1940.\n\nUncle Billy passed away at his home on June 20, 1956, and left his personal estate to the Fraternity, which founded the Phillips Fund within the Sigma Phi Epsilon Educational Foundation. That fund provides scholarships for members of the University of Richmond chapter.\n\nHe loved his Fraternity intensely and had attended every one of the 24 Conclaves from the first at Richmond College, 1903, to Cincinnati, 1955. William L. Phillips must be numbered among the first handful of truly great builders of the American college fraternity system. No one has achieved a greater record.");
        $member->setPhotoUrl('https://sigep.org/wp-content/uploads/2016/11/phillips.jpg');
        $manager->persist($member);

        $this->addReference(self::UNCLE_BILLY, $member);

        $member = new Member();
        $member->setExternalIdentifier(md5('1-0007'));
        $member->setLocalIdentifier('1-0007');
        $member->setFirstName('Lucian');
        $member->setPreferredName('Lucian');
        $member->setMiddleName('Baum');
        $member->setLastName('Cox');
        $member->setPrimaryEmail('lcox@example.com');
        $member->setBirthDate(new \DateTime('1879-11-13'));
        $member->setClassYear(1902);
        $member->setStatus($memberStatusAlumnus);
        $member->setJoinDate(new \DateTime('November 1, 1901'));
        $member->setDirectoryNotes("Lucian Baum Cox was born on November 13, 1879, in Princess Anne County, Virginia.\n\nHe attended a one-room public school, and worked at his father's farm and sawmill. In September, 1898, he entered Richmond College, first as an academic student and later as a law student, where he received a bachelor of law degree in June, 1902.\n\nAs an undergraduate, he taught Bible class in Calvary Baptist Church on Sunday mornings and to a group of inmates at the Virginia Penitentiary in the afternoons. In July, 1902, he began the practice of law in Norfolk, Virginia.\n\nFounder Cox wrote the application for the corporate charter for Sigma Phi Epsilon. In 1939, he published his first edition of Titles to Land in Virginia, and a second edition was published in 1947. This book was followed in 1951 by his work on Principles and Procedure in Equity. Brother Cox died in Norfolk, Virginia on June 10, 1971, at the age of 91.");
        $manager->persist($member);

        $member = new Member();
        $member->setExternalIdentifier(md5('1-0008'));
        $member->setLocalIdentifier('1-0008');
        $member->setFirstName('Richard');
        $member->setPreferredName('Richard');
        $member->setMiddleName('Spurgeon');
        $member->setLastName('Owens');
        $member->setPrimaryEmail('rowens@example.com');
        $member->setBirthDate(new \DateTime('1880-10-28'));
        $member->setClassYear(1904);
        $member->setStatus($memberStatusMember);
        $member->setJoinDate(new \DateTime('November 1, 1901'));
        $member->setDirectoryNotes("Richard Spurgeon Owens was a minister's son, and was born October 28, 1880, in Hempstead, King George County, Virginia.\n\nWhen he graduated from Richmond in 1904, he spent four years at Colgate Theological Seminary, to become a minister, graduating in 1907. His career in the ministry called him to Baptist churches in Washington, D.C., Roanoke, Virginia, and for four years, 1917-1921, as an instructor in Fishburn Military Academy in Waynesboro, Virginia. Before his death on July 6, 1950, he was trustee of the University of Richmond, Bluefield College, and also of the Baptist Orphanage in Salem, Virginia.");
        $manager->persist($member);

        $member = new Member();
        $member->setExternalIdentifier(md5('1-0009'));
        $member->setLocalIdentifier('1-0009');
        $member->setFirstName('Edgar');
        $member->setPreferredName('Edgar');
        $member->setMiddleName('Lee');
        $member->setLastName('Allen');
        $member->setPrimaryEmail('eallen@example.com');
        $member->setBirthDate(new \DateTime('1880-01-06'));
        $member->setClassYear(1902);
        $member->setStatus($memberStatusMember);
        $member->setJoinDate(new \DateTime('November 1, 1901'));
        $member->setDirectoryNotes("Edgar Lee Allen was born on January 6, 1880, in Virginia.\n\nHe attended private schools in King and Queen County. After three liberal arts years at Richmond, he completed graduate work in law in 1902. He moved to Birmingham, Alabama, in October, 1902. After taking up residence in Birmingham, Founder Allen practiced law in that city steadily, serving as a judge in various courts until his death on March 21, 1945.");
        $member->addTag($tag1901Club);
        $manager->persist($member);

        $member = new Member();
        $member->setExternalIdentifier(md5('1-0010'));
        $member->setLocalIdentifier('1-0010');
        $member->setFirstName('Robert');
        $member->setPreferredName('Bob');
        $member->setMiddleName('Alfred');
        $member->setLastName('McFarland');
        $member->setPrimaryEmail('rmcfarland@example.com');
        $member->setBirthDate(new \DateTime('1876-01-31'));
        $member->setClassYear(1902);
        $member->setStatus($memberStatusMember);
        $member->setJoinDate(new \DateTime('November 1, 1901'));
        $member->setDirectoryNotes("Robert Alfred McFarland was born on a farm near Oxford, North Carolina, on January 31, 1876.\n\nHe attended Granville County public schools; three years at Bethel Hill Institute, North Carolina; four years at Richmond College—received a B.S. in 1902; received a bachelors of theology degree from the Southern Baptist Theological Seminary at Louisville in 1908, and an honorary doctor of divinity degree from the University of Richmond in 1921.\n\nMcFarland made the motion to found Sigma Phi Epsilon.\n\nMcFarland held important pastorates in three states. In North Carolina, he was a member of the Baptist State Board, a trustee of the Baptist Orphanage and Wake Forest College, and was vice president of the Baptist State Convention. In Virginia, he served as a member of the Baptist State Board, a trustee of the Baptist Hospital, the Fork Union Military Academy, and the Southern Baptist State Convention.\n\nMcFarland was once written up in a London journal as a \"representative\" minister of the United States. He died on March 14, 1960.");
        $manager->persist($member);

        $member = new Member();
        $member->setExternalIdentifier(md5('1-0011'));
        $member->setLocalIdentifier('1-0011');
        $member->setFirstName('Frank');
        $member->setPreferredName('Frank');
        $member->setMiddleName('Webb');
        $member->setLastName('Kerfoot');
        $member->setPrimaryEmail('fkerfoot@example.com');
        $member->setBirthDate(new \DateTime('1876-10-02'));
        $member->setClassYear(1902);
        $member->setStatus($memberStatusMember);
        $member->setJoinDate(new \DateTime('November 1, 1901'));
        $member->setDirectoryNotes("Frank Webb Kerfoot, who died in an accident on August 29, 1918, was another Baptist preacher.\n\nA native Virginian, he was born October 2, 1876, in Buckland, Prince William County, and at Richmond was a member of the Class of 1902. At the time of his death, he was a chaplain in the Army. He had been pastor of parishes in Buckingham and Middlesex Counties, and Chatham, Virginia; Nowata, Oklahoma, and Fort Smith, Arkansas.");
        $member->addTag($tag1901Club);
        $member->setIsDeceased(true);
        $member->setPhotoUrl('https://sigep.org/wp-content/uploads/2016/11/kerfoot.jpg');
        $manager->persist($member);

        $member = new Member();
        $member->setExternalIdentifier(md5('1-0012'));
        $member->setLocalIdentifier('1-0012');
        $member->setFirstName('Thomas');
        $member->setPreferredName('Tom');
        $member->setMiddleName('Vaden');
        $member->setLastName('McCaul');
        $member->setPrimaryEmail('tmccaul@example.com');
        $member->setBirthDate(new \DateTime('1878-11-25'));
        $member->setClassYear(1902);
        $member->setStatus($memberStatusMember);
        $member->setJoinDate(new \DateTime('November 1, 1901'));
        $member->setDirectoryNotes("Thomas Vaden McCaul was born in Charles City County, Virginia on November 25, 1878.\n\nHe attended Richmond public schools, graduated from Richmond High School, and entered Richmond College as a pre-law student in February, 1898. In September of that year, Uncle Tom returned to Richmond College as a ministerial student, being convinced of a call to preach. He received his B.A. from Richmond College in June, 1902; the masters of theology from the Southern Baptist Theological Seminary in 1905, and the M.A. from the University of Virginia in 1908. The honorary degree of doctor of divinity was conferred upon him by the University of Richmond and Stetson University.\n\nWhile at Richmond College, Uncle Tom was active in debates and oratorical contests. He won the writer's medal offered by his literary society his senior year. He won the orator's medal at the University of Virginia in 1907. Uncle Tom served as the first president of Virginia Alpha in 1901-1902 and wrote the Fraternity's first song, \"Our Fraternity,\" in 1902. In the fall of 1902, he visited Bethany College, West Virginia; Washington and Jefferson College, Pennsylvania; and West Virginia University and formed a nucleus for chapters in all three. He helped establish Virginia Eta at the University of Virginia in 1907 and Florida Alpha at the University of Florida in 1925. He was appointed National Chaplain in 1947 and served until 1959.\n\nUncle Tom served as pastor of Baptist churches in Kentucky, Virginia, South Carolina, and Florida. After more than 2 years as pastor of the First Baptist Church of Gainesville, Florida, he retired on January 1, 1949. He remained in Gainesville, frequently looking in on his young Florida Alpha brothers. He continued to attend Conclaves, his last being the 32nd Grand Chapter in Atlanta in 1971. On November 18, 1972, he died peacefully in Gainesville at the age of 93. He was the Fraternity's last remaining founder.");
        $member->setPhotoUrl('https://sigep.org/wp-content/uploads/2016/11/mccaul.jpg');
        $manager->persist($member);

        $member = new Member();
        $member->setExternalIdentifier(md5('1-0013'));
        $member->setLocalIdentifier('1-0013');
        $member->setFirstName('Bad<img src=x onerror="alert(\'xss\')" />');
        $member->setLastName('Actor<img src=x onerror="alert(\'xss\')" />');
        $member->setPrimaryEmail('bad.actor@example.com');
        $member->setBirthDate(null);
        $member->setClassYear(2021);
        $member->setPrimaryTelephoneNumber('<img src=x onerror="alert(\'xss\')" />');
        $member->setStatus($memberStatusExpelled);
        $member->setJoinDate(new \DateTime());
        $member->setDirectoryNotes('Totally legit. <img src=x onerror="alert(\'xss\')" />');
        $member->setPhotoUrl('https://octodex.github.com/images/privateinvestocat.jpg');
        $manager->persist($member);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            TagFixtures::class,
            MemberStatusFixtures::class
        ];
    }

}
